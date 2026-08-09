<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RobotCommand;
use App\Services\RobotArmCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RobotArmController extends Controller
{
    public function index(RobotArmCommandService $robot): View
    {
        $recentCommands = $this->recentCommands();
        $activeCommand = $robot->currentQueueCommand()?->load('order');

        $orders = Order::with(['orderItems.product'])
            ->where('status', 'confirmed')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (Order $order) use ($robot): array {
                return [
                    'id' => $order->public_id,
                    'number' => $order->order_number,
                    'customer' => $order->customer_name ?: ($order->user?->name ?? 'Customer'),
                    'status' => $order->status,
                    'location' => $robot->resolveOrderLocation($order),
                ];
            });

        return view('seller.robot-arm', [
            'activeCommand' => $activeCommand,
            'recentCommands' => $recentCommands,
            'orders' => $orders,
            'locations' => config('robot.locations', range(1, 5)),
            'robotConfigured' => $robot->isConfigured(),
            'robotBaseUrl' => config('robot.base_url'),
        ]);
    }

    public function status(RobotArmCommandService $robot): JsonResponse
    {
        $status = $robot->processQueue();

        // An empty queue only means the worker is healthy; probe ESP32 before reporting connectivity.
        if (($status['queue_empty'] ?? false) && $status['command'] === null) {
            $status = $robot->pollStatus();
        }

        return response()->json([
            'success' => true,
            'robot' => [
                'configured' => $status['configured'],
                'online' => $status['ok'],
                'status' => $status['status'],
                'message' => $status['message'],
                'data' => $status['data'],
            ],
            'active_command' => $this->serializeCommand($status['command'] ?: $robot->currentQueueCommand()),
            'recent_commands' => $this->recentCommands()->map(fn (RobotCommand $command) => $this->serializeCommand($command))->values(),
        ]);
    }

    public function pick(Request $request, RobotArmCommandService $robot): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'integer', \Illuminate\Validation\Rule::in(config('robot.locations', range(1, 5)))],
        ]);

        $order = Order::with(['orderItems.product'])
            ->wherePublicIdOrId($validated['order_id'])
            ->orWhere('order_number', $validated['order_id'])
            ->firstOrFail();

        $command = $robot->dispatchPickForOrderIfNeeded($order, isset($validated['location']) ? (int) $validated['location'] : null);

        return response()->json([
            'success' => ! in_array($command->status, [RobotCommand::STATUS_ERROR, RobotCommand::STATUS_STOPPED], true),
            'message' => $command->error ?: 'Order added to the robot queue.',
            'command' => $this->serializeCommand($command),
        ], $command->status === RobotCommand::STATUS_ERROR ? 422 : 200);
    }

    public function home(RobotArmCommandService $robot): JsonResponse
    {
        $command = $robot->dispatchSimple('HOME');

        return response()->json([
            'success' => $command->status !== RobotCommand::STATUS_ERROR,
            'message' => $command->error ?: 'HOME command sent to robot arm.',
            'command' => $this->serializeCommand($command),
        ], $command->status === RobotCommand::STATUS_ERROR ? 422 : 200);
    }

    public function stop(RobotArmCommandService $robot): JsonResponse
    {
        $command = $robot->dispatchSimple('STOP');

        return response()->json([
            'success' => $command->status !== RobotCommand::STATUS_ERROR,
            'message' => $command->error ?: 'STOP command sent to robot arm.',
            'command' => $this->serializeCommand($command),
        ], $command->status === RobotCommand::STATUS_ERROR ? 422 : 200);
    }

    private function recentCommands()
    {
        return RobotCommand::with('order')
            ->latest()
            ->limit(12)
            ->get();
    }

    private function serializeCommand(?RobotCommand $command): ?array
    {
        if (! $command) {
            return null;
        }

        return [
            'id' => $command->public_id,
            'order_reference' => $command->order_reference,
            'command' => $command->command,
            'location' => $command->location,
            'sequence' => $command->sequence,
            'total' => $command->total,
            'status' => $command->status,
            'error' => $command->error,
            'created_at' => optional($command->created_at)->format('M d, H:i:s'),
            'last_polled_at' => optional($command->last_polled_at)->format('M d, H:i:s'),
            'completed_at' => optional($command->completed_at)->format('M d, H:i:s'),
        ];
    }
}
