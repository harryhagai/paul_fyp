<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClickPesaOrderSyncService
{
    public function __construct(
        private readonly ClickPesaService $clickPesaService,
        private readonly RobotArmCommandService $robot
    )
    {
    }

    public function syncRecentProcessingOrdersForUser(int $userId, int $limit = 5): void
    {
        Order::query()
            ->where('user_id', $userId)
            ->where('payment_provider', 'clickpesa')
            ->whereIn('payment_status', ['pending', 'processing'])
            ->latest('ordered_at')
            ->take($limit)
            ->get()
            ->each(fn (Order $order) => $this->syncOrder($order));
    }

    public function syncAllProcessingOrders(int $limit = 50): int
    {
        $synced = 0;

        Order::query()
            ->where('payment_provider', 'clickpesa')
            ->whereIn('payment_status', ['pending', 'processing'])
            ->latest('ordered_at')
            ->take($limit)
            ->get()
            ->each(function (Order $order) use (&$synced): void {
                if ($this->syncOrder($order)) {
                    $synced++;
                }
            });

        return $synced;
    }

    public function syncOrder(Order $order): bool
    {
        if ($order->payment_provider !== 'clickpesa' || !in_array($order->payment_status, ['pending', 'processing'], true)) {
            return false;
        }

        try {
            $payments = $this->clickPesaService->queryPaymentStatus($this->paymentLookupReference($order));
        } catch (\Throwable $e) {
            Log::warning('ClickPesa order status sync failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        $payment = $this->pickPayment($payments, $order);

        if (!$payment) {
            return false;
        }

        $shouldDispatchRobot = $this->applyPaymentData($order, $payment);

        if ($shouldDispatchRobot) {
            try {
                $this->robot->dispatchPickForOrderIfNeeded($order->fresh(['orderItems.product']));
            } catch (Throwable $e) {
                Log::error('Automatic robot PICK dispatch failed after ClickPesa sync', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return true;
    }

    private function pickPayment(array $payments, Order $order): ?array
    {
        $items = array_is_list($payments) ? $payments : [$payments];
        $cleanOrderNumber = preg_replace('/[^A-Za-z0-9]/', '', (string) $order->order_number);
        $payloadReference = is_array($order->clickpesa_payload ?? null)
            ? (string) ($order->clickpesa_payload['orderReference'] ?? '')
            : '';

        foreach ($items as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $paymentId = (string) ($payment['id'] ?? $payment['paymentId'] ?? '');
            $orderReference = (string) ($payment['orderReference'] ?? $payment['order_reference'] ?? '');

            if (
                $paymentId === $order->clickpesa_payment_id
                || ($payloadReference !== '' && $orderReference === $payloadReference)
                || $orderReference === $cleanOrderNumber
                || str_starts_with($orderReference, $cleanOrderNumber . 'R')
            ) {
                return $payment;
            }
        }

        return is_array($items[0] ?? null) ? $items[0] : null;
    }

    private function applyPaymentData(Order $order, array $payment): bool
    {
        return DB::transaction(function () use ($order, $payment): bool {
            $order = Order::with('orderItems.product')->lockForUpdate()->find($order->id);

            if (!$order) {
                return false;
            }

            $wasConfirmed = $order->status === 'confirmed'
                && in_array(strtolower((string) $order->payment_status), ['paid', 'successful', 'success', 'completed'], true);

            $status = strtoupper(trim((string) ($payment['status'] ?? $payment['paymentStatus'] ?? $payment['state'] ?? '')));
            $message = $payment['message'] ?? $payment['description'] ?? $payment['statusDescription'] ?? $order->payment_message;

            $order->clickpesa_payload = $payment;
            $order->clickpesa_payment_id = $payment['id'] ?? $payment['paymentId'] ?? $payment['payment_id'] ?? $order->clickpesa_payment_id;
            $order->clickpesa_payment_reference = $payment['paymentReference'] ?? $payment['payment_reference'] ?? $payment['providerReference'] ?? $order->clickpesa_payment_reference;
            $order->clickpesa_channel = $payment['channel'] ?? $order->clickpesa_channel;
            $order->payment_message = $message;

            if ($this->isSuccessfulPayment($status)) {
                $order->payment_status = 'paid';
                $order->status = 'confirmed';
                $order->paid_at = $order->paid_at ?: now();
            }

            if ($this->isFailedPayment($status)) {
                $order->payment_status = 'failed';
                $order->payment_failed_at = $order->payment_failed_at ?: now();
            }

            if (in_array($status, ['PROCESSING', 'PENDING', 'INITIATED', 'IN_PROGRESS'], true)) {
                $order->payment_status = strtolower($status);
            }

            $order->save();

            return !$wasConfirmed
                && $order->status === 'confirmed'
                && $order->payment_status === 'paid';
        });
    }

    private function isSuccessfulPayment(string $status): bool
    {
        return in_array($status, ['SUCCESS', 'SUCCESSFUL', 'PAID', 'COMPLETED', 'COMPLETE', 'SETTLED', 'APPROVED'], true);
    }

    private function isFailedPayment(string $status): bool
    {
        return in_array($status, ['FAILED', 'FAILURE', 'CANCELLED', 'CANCELED', 'REJECTED', 'EXPIRED', 'TIMEOUT'], true);
    }

    private function paymentLookupReference(Order $order): string
    {
        $payloadReference = is_array($order->clickpesa_payload ?? null)
            ? ($order->clickpesa_payload['orderReference'] ?? null)
            : null;

        return $payloadReference ?: $order->order_number;
    }
}
