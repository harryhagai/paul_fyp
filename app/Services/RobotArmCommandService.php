<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RobotCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RobotArmCommandService
{
    public function __construct(private readonly RobotArmClient $client) {}

    /**
     * Send one PICK immediately. Automatic order processing should use the queue method below.
     */
    public function dispatchPickForOrder(Order $order, ?int $location = null): RobotCommand
    {
        $order->loadMissing('orderItems.product');
        $locationError = null;

        if ($location === null) {
            $resolution = $this->resolveOrderLocationDetails($order);
            $location = $resolution['location'];
            $locationError = $resolution['error'];
        } elseif (! $this->isAllowedLocation($location)) {
            $locationError = 'INVALID_LOCATION';
        }

        $command = $this->createPickCommand($order, $location, RobotCommand::STATUS_PENDING);

        if ($locationError !== null) {
            $command->markFromRobotResponse([
                'status' => RobotCommand::STATUS_ERROR,
                'order_id' => $order->order_number,
                'error' => $locationError,
            ]);

            return $command;
        }

        return $this->sendPickCommand($command);
    }

    /**
     * Add every physical item in an order to the persistent FIFO queue exactly once.
     */
    public function dispatchPickForOrderIfNeeded(Order $order, ?int $location = null): RobotCommand
    {
        return DB::transaction(function () use ($order, $location): RobotCommand {
            $lockedOrder = Order::with('orderItems.product')
                ->lockForUpdate()
                ->findOrFail($order->id);

            $existingCommand = RobotCommand::query()
                ->where('order_id', $lockedOrder->id)
                ->where('command', 'PICK')
                ->whereIn('status', [
                    RobotCommand::STATUS_QUEUED,
                    ...RobotCommand::activeStatuses(),
                ])
                ->oldest('id')
                ->first();

            if ($existingCommand) {
                return $existingCommand;
            }

            if ($lockedOrder->status === 'completed') {
                $completedCommand = RobotCommand::query()
                    ->where('order_id', $lockedOrder->id)
                    ->where('command', 'PICK')
                    ->where('status', RobotCommand::STATUS_COMPLETED)
                    ->latest('id')
                    ->first();

                if ($completedCommand) {
                    return $completedCommand;
                }
            }

            $resolution = $this->resolvePickCycles($lockedOrder, $location);
            if ($resolution['error'] !== null) {
                $command = $this->createPickCommand($lockedOrder, null, RobotCommand::STATUS_PENDING);
                $command->markFromRobotResponse([
                    'status' => RobotCommand::STATUS_ERROR,
                    'order_id' => $lockedOrder->order_number,
                    'error' => $resolution['error'],
                ]);

                return $command;
            }

            $batchId = (string) Str::uuid();
            $total = count($resolution['cycles']);
            $firstCommand = null;

            foreach ($resolution['cycles'] as $index => $cycle) {
                $command = $this->createPickCommand(
                    $lockedOrder,
                    $cycle['location'],
                    RobotCommand::STATUS_QUEUED,
                    [
                        'batch_id' => $batchId,
                        'order_item_id' => $cycle['order_item_id'],
                        'sequence' => $index + 1,
                        'total' => $total,
                    ],
                );

                $firstCommand ??= $command;
            }

            return $firstCommand;
        });
    }

    /**
     * Poll the current command and start the next queued cycle when the robot is free.
     */
    public function processQueue(): array
    {
        if (! $this->isConfigured()) {
            return $this->queueResult(false, 'ERROR', null, 'Robot arm endpoint is not configured.');
        }

        $lock = Cache::lock('robot-arm-command-queue', max(10, $this->queueLockSeconds()));
        if (! $lock->get()) {
            return $this->queueResult(true, 'PENDING', $this->currentQueueCommand(), 'Robot queue worker is already running.');
        }

        try {
            $lastResult = null;
            $activeCommand = $this->activeCommand();

            if ($activeCommand) {
                $lastResult = $this->pollStatus();
                $activeCommand->refresh();

                if (in_array($activeCommand->status, RobotCommand::activeStatuses(), true)) {
                    return $lastResult;
                }

                if (in_array($activeCommand->status, [RobotCommand::STATUS_ERROR, RobotCommand::STATUS_STOPPED], true)) {
                    $this->failRemainingBatchCommands($activeCommand);
                }
            }

            $nextCommand = RobotCommand::query()
                ->where('command', 'PICK')
                ->where('status', RobotCommand::STATUS_QUEUED)
                ->oldest('id')
                ->first();

            if (! $nextCommand) {
                if ($lastResult !== null) {
                    $lastResult['queue_empty'] = true;

                    return $lastResult;
                }

                return $this->queueResult(true, RobotCommand::STATUS_IDLE, null, 'Robot queue is empty.', true);
            }

            $nextCommand->forceFill([
                'status' => RobotCommand::STATUS_PENDING,
                'error' => null,
                'failed_at' => null,
            ])->save();

            $nextCommand = $this->sendPickCommand($nextCommand);
            if (in_array($nextCommand->status, [RobotCommand::STATUS_ERROR, RobotCommand::STATUS_STOPPED], true)) {
                $this->failRemainingBatchCommands($nextCommand);
            }

            return $this->resultFromCommand($nextCommand);
        } finally {
            $lock->release();
        }
    }

    public function dispatchSimple(string $commandName): RobotCommand
    {
        $commandName = strtoupper($commandName);

        $command = RobotCommand::create([
            'command' => $commandName,
            'status' => RobotCommand::STATUS_PENDING,
            'request_payload' => ['command' => $commandName],
        ]);

        try {
            $result = match ($commandName) {
                'HOME' => $this->client->home(),
                'STOP' => $this->client->stop(),
                'STATUS' => $this->client->status(),
                default => $this->client->send($commandName),
            };
        } catch (Throwable $exception) {
            $result = [
                'data' => [
                    'status' => RobotCommand::STATUS_ERROR,
                    'error' => 'COMMAND_FAILED',
                ],
                'message' => $exception->getMessage(),
            ];
        }

        $data = $result['data'] ?? [];
        if (($result['ok'] ?? false) === false && empty($data['error'])) {
            $data['error'] = $result['message'] ?? 'COMMAND_FAILED';
        }

        $command->request_payload = $result['payload'] ?? $command->request_payload;
        $command->save();
        $command->markFromRobotResponse($data);

        return $command;
    }

    public function pollStatus(): array
    {
        $result = $this->client->status();
        $data = $result['data'] ?? [];
        $status = strtoupper((string) ($data['status'] ?? RobotCommand::STATUS_ERROR));

        $command = null;
        if (
            ($result['ok'] ?? false) === true
            && ($result['valid_response'] ?? false) === true
            && $status !== RobotCommand::STATUS_IDLE
        ) {
            $command = $this->findCommandForStatus($data);
        }

        if ($command) {
            $command->markFromRobotResponse($data);
            $this->applyOrderStatusFromCommand($command);
        }

        return [
            'ok' => (bool) ($result['ok'] ?? false),
            'configured' => (bool) ($result['configured'] ?? false),
            'status' => $status,
            'data' => $data,
            'message' => $result['message'] ?? null,
            'command' => $command,
            'queue_empty' => false,
        ];
    }

    public function resolveOrderLocation(Order $order): ?int
    {
        $order->loadMissing('orderItems.product');

        return $this->resolveOrderLocationDetails($order)['location'];
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function hasActiveCommand(): bool
    {
        return $this->activeCommand() !== null;
    }

    public function hasQueuedCommand(): bool
    {
        return RobotCommand::query()
            ->where('command', 'PICK')
            ->where('status', RobotCommand::STATUS_QUEUED)
            ->exists();
    }

    public function currentQueueCommand(): ?RobotCommand
    {
        return $this->activeCommand() ?: RobotCommand::query()
            ->where('command', 'PICK')
            ->where('status', RobotCommand::STATUS_QUEUED)
            ->oldest('id')
            ->first();
    }

    private function sendPickCommand(RobotCommand $command): RobotCommand
    {
        try {
            $result = $this->client->pick((string) $command->order_reference, (int) $command->location);
        } catch (Throwable $exception) {
            $result = [
                'data' => [
                    'status' => RobotCommand::STATUS_ERROR,
                    'order_id' => $command->order_reference,
                    'location' => $command->location,
                    'error' => 'COMMAND_FAILED',
                ],
                'message' => $exception->getMessage(),
            ];
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        if (($result['valid_response'] ?? false) === true) {
            $responseOrder = isset($data['order_id']) ? (string) $data['order_id'] : null;
            $responseLocation = isset($data['location']) ? (int) $data['location'] : null;

            if ($responseOrder !== $command->order_reference || ($responseLocation !== null && $responseLocation !== $command->location)) {
                $data = [
                    'status' => RobotCommand::STATUS_ERROR,
                    'order_id' => $command->order_reference,
                    'location' => $command->location,
                    'error' => 'INVALID_ROBOT_RESPONSE',
                ];
            }
        }

        $data['order_id'] ??= $command->order_reference;
        $data['location'] ??= $command->location;

        if (($data['status'] ?? null) === RobotCommand::STATUS_STOPPED && empty($data['error'])) {
            $data['error'] = 'ROBOT_STOPPED';
        }
        if (($result['ok'] ?? false) === false && empty($data['error'])) {
            $data['error'] = $result['message'] ?? 'COMMAND_FAILED';
        }

        $command->request_payload = array_merge(
            $command->request_payload ?? [],
            $result['payload'] ?? [],
        );
        $command->save();

        if ($command->batch_id && ($data['error'] ?? null) === 'ROBOT_BUSY') {
            $command->forceFill([
                'status' => RobotCommand::STATUS_QUEUED,
                'error' => 'ROBOT_BUSY',
                'response_payload' => $data,
                'last_polled_at' => now(),
                'failed_at' => null,
            ])->save();

            return $command;
        }

        $command->markFromRobotResponse($data);
        $this->applyOrderStatusFromCommand($command);

        return $command;
    }

    private function createPickCommand(
        Order $order,
        ?int $location,
        string $status,
        array $queueData = [],
    ): RobotCommand {
        return RobotCommand::create([
            'order_id' => $order->id,
            'order_item_id' => $queueData['order_item_id'] ?? null,
            'order_reference' => $order->order_number,
            'batch_id' => $queueData['batch_id'] ?? null,
            'command' => 'PICK',
            'location' => $location,
            'sequence' => $queueData['sequence'] ?? null,
            'total' => $queueData['total'] ?? null,
            'status' => $status,
            'request_payload' => [
                'command' => 'PICK',
                'order_id' => $order->order_number,
                'location' => $location,
            ],
        ]);
    }

    private function activeCommand(): ?RobotCommand
    {
        return RobotCommand::query()
            ->whereIn('status', RobotCommand::activeStatuses())
            ->oldest('id')
            ->first();
    }

    private function findCommandForStatus(array $data): ?RobotCommand
    {
        $query = RobotCommand::query()
            ->whereIn('status', RobotCommand::activeStatuses());

        if (! empty($data['order_id'])) {
            $query->where('order_reference', (string) $data['order_id']);
        }

        return $query->oldest('id')->first();
    }

    private function applyOrderStatusFromCommand(RobotCommand $command): void
    {
        if (
            $command->command !== 'PICK'
            || $command->status !== RobotCommand::STATUS_COMPLETED
            || ! $command->order
            || $command->order->status !== 'confirmed'
        ) {
            return;
        }

        if ($command->batch_id) {
            $batchHasUnfinishedCommands = RobotCommand::query()
                ->where('batch_id', $command->batch_id)
                ->where('command', 'PICK')
                ->where('status', '!=', RobotCommand::STATUS_COMPLETED)
                ->exists();

            if ($batchHasUnfinishedCommands) {
                return;
            }
        }

        $command->order->forceFill(['status' => 'completed'])->save();
    }

    private function failRemainingBatchCommands(RobotCommand $failedCommand): void
    {
        if (! $failedCommand->batch_id) {
            return;
        }

        RobotCommand::query()
            ->where('batch_id', $failedCommand->batch_id)
            ->where('status', RobotCommand::STATUS_QUEUED)
            ->update([
                'status' => RobotCommand::STATUS_ERROR,
                'error' => 'PREVIOUS_PICK_FAILED',
                'failed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function resolvePickCycles(Order $order, ?int $overrideLocation = null): array
    {
        if ($order->orderItems->isEmpty()) {
            return ['cycles' => [], 'error' => 'LOCATION_NOT_ASSIGNED'];
        }

        $cycles = [];

        foreach ($order->orderItems as $item) {
            $quantity = (int) $item->quantity;
            if ($quantity < 1) {
                return ['cycles' => [], 'error' => 'INVALID_QUANTITY'];
            }

            $location = $overrideLocation ?? $item->product?->robot_location;
            if ($location === null) {
                return ['cycles' => [], 'error' => 'LOCATION_NOT_ASSIGNED'];
            }

            $location = (int) $location;
            if (! $this->isAllowedLocation($location)) {
                return ['cycles' => [], 'error' => 'INVALID_LOCATION'];
            }

            for ($cycle = 0; $cycle < $quantity; $cycle++) {
                $cycles[] = [
                    'order_item_id' => $item->id,
                    'location' => $location,
                ];
            }
        }

        return ['cycles' => $cycles, 'error' => null];
    }

    private function resolveOrderLocationDetails(Order $order): array
    {
        if ($order->orderItems->isEmpty()) {
            return ['location' => null, 'error' => 'LOCATION_NOT_ASSIGNED'];
        }

        if ($order->orderItems->count() !== 1 || (int) $order->orderItems->first()->quantity !== 1) {
            return ['location' => null, 'error' => 'MULTIPLE_PICKS_NOT_SUPPORTED'];
        }

        $location = $order->orderItems->first()->product?->robot_location;
        if ($location === null) {
            return ['location' => null, 'error' => 'LOCATION_NOT_ASSIGNED'];
        }

        $location = (int) $location;
        if (! $this->isAllowedLocation($location)) {
            return ['location' => null, 'error' => 'INVALID_LOCATION'];
        }

        return ['location' => $location, 'error' => null];
    }

    private function resultFromCommand(RobotCommand $command): array
    {
        $ok = ! in_array($command->status, [RobotCommand::STATUS_ERROR, RobotCommand::STATUS_STOPPED], true);

        return [
            'ok' => $ok,
            'configured' => true,
            'status' => $command->status,
            'data' => $command->response_payload ?? [],
            'message' => $command->error,
            'command' => $command,
            'queue_empty' => false,
        ];
    }

    private function queueResult(bool $ok, string $status, ?RobotCommand $command, ?string $message, bool $queueEmpty = false): array
    {
        return [
            'ok' => $ok,
            'configured' => $this->isConfigured(),
            'status' => $status,
            'data' => [],
            'message' => $message,
            'command' => $command,
            'queue_empty' => $queueEmpty,
        ];
    }

    private function queueLockSeconds(): int
    {
        return ((int) config('robot.timeout', 5) * 2) + 5;
    }

    private function isAllowedLocation(int $location): bool
    {
        $locations = array_map('intval', (array) config('robot.locations', range(1, 5)));

        return in_array($location, $locations, true);
    }
}
