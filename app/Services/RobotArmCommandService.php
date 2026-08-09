<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RobotCommand;
use Throwable;

class RobotArmCommandService
{
    public function __construct(private readonly RobotArmClient $client) {}

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

        $command = RobotCommand::create([
            'order_id' => $order->id,
            'order_reference' => $order->order_number,
            'command' => 'PICK',
            'location' => $location,
            'status' => RobotCommand::STATUS_PENDING,
            'request_payload' => [
                'command' => 'PICK',
                'order_id' => $order->order_number,
                'location' => $location,
            ],
        ]);

        if ($locationError !== null) {
            $command->markFromRobotResponse([
                'status' => 'ERROR',
                'order_id' => $order->order_number,
                'error' => $locationError,
            ]);

            return $command;
        }

        try {
            $result = $this->client->pick($order->order_number, $location);
        } catch (Throwable $exception) {
            $result = [
                'data' => [
                    'status' => 'ERROR',
                    'order_id' => $order->order_number,
                    'location' => $location,
                    'error' => 'COMMAND_FAILED',
                ],
                'message' => $exception->getMessage(),
            ];
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        if (($result['valid_response'] ?? false) === true) {
            $responseOrder = isset($data['order_id']) ? (string) $data['order_id'] : null;
            $responseLocation = isset($data['location']) ? (int) $data['location'] : null;

            if ($responseOrder !== $order->order_number || ($responseLocation !== null && $responseLocation !== $location)) {
                $data = [
                    'status' => 'ERROR',
                    'order_id' => $order->order_number,
                    'location' => $location,
                    'error' => 'INVALID_ROBOT_RESPONSE',
                ];
            }
        }

        if (! isset($data['order_id'])) {
            $data['order_id'] = $order->order_number;
        }
        if (! isset($data['location'])) {
            $data['location'] = $location;
        }
        if (($data['status'] ?? null) === RobotCommand::STATUS_STOPPED && empty($data['error'])) {
            $data['error'] = 'ROBOT_STOPPED';
        }
        if (($result['ok'] ?? false) === false && empty($data['error'])) {
            $data['error'] = $result['message'] ?? 'COMMAND_FAILED';
        }

        $command->request_payload = $result['payload'] ?? $command->request_payload;
        $command->save();
        $command->markFromRobotResponse($data);
        $this->applyOrderStatusFromCommand($command);

        return $command;
    }

    public function dispatchPickForOrderIfNeeded(Order $order, ?int $location = null): RobotCommand
    {
        $existingCommand = RobotCommand::query()
            ->where('order_id', $order->id)
            ->where('command', 'PICK')
            ->whereIn('status', [
                ...RobotCommand::activeStatuses(),
                RobotCommand::STATUS_COMPLETED,
            ])
            ->latest()
            ->first();

        if ($existingCommand) {
            return $existingCommand;
        }

        return $this->dispatchPickForOrder($order, $location);
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
                    'status' => 'ERROR',
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
        $status = strtoupper((string) ($data['status'] ?? 'ERROR'));

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
        return RobotCommand::query()
            ->whereIn('status', RobotCommand::activeStatuses())
            ->exists();
    }

    private function findCommandForStatus(array $data): ?RobotCommand
    {
        $orderReference = $data['order_id'] ?? null;
        if ($orderReference) {
            $command = RobotCommand::query()
                ->where('order_reference', $orderReference)
                ->latest()
                ->first();

            if ($command) {
                return $command;
            }

            return null;
        }

        return RobotCommand::query()
            ->whereIn('status', RobotCommand::activeStatuses())
            ->latest()
            ->first();
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

        $command->order->forceFill([
            'status' => 'completed',
        ])->save();
    }

    private function resolveOrderLocationDetails(Order $order): array
    {
        if ($order->orderItems->isEmpty()) {
            return ['location' => null, 'error' => 'LOCATION_NOT_ASSIGNED'];
        }

        if (
            $order->orderItems->count() !== 1
            || (int) $order->orderItems->first()->quantity !== 1
        ) {
            return ['location' => null, 'error' => 'MULTIPLE_PICKS_NOT_SUPPORTED'];
        }

        $assignedLocations = $order->orderItems
            ->map(fn ($item) => $item->product?->robot_location);

        if ($assignedLocations->contains(fn ($location) => $location === null)) {
            return ['location' => null, 'error' => 'LOCATION_NOT_ASSIGNED'];
        }

        $locations = $assignedLocations
            ->map(fn ($location) => (int) $location)
            ->unique()
            ->values();

        if ($locations->count() > 1) {
            return ['location' => null, 'error' => 'MULTIPLE_LOCATIONS_NOT_SUPPORTED'];
        }

        $location = $locations->first();
        if ($location === null || ! $this->isAllowedLocation($location)) {
            return ['location' => null, 'error' => 'INVALID_LOCATION'];
        }

        return ['location' => $location, 'error' => null];
    }

    private function isAllowedLocation(int $location): bool
    {
        $locations = array_map('intval', (array) config('robot.locations', range(1, 5)));

        return in_array($location, $locations, true);
    }
}
