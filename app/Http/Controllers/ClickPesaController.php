<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ClickPesaService;
use App\Services\RobotArmCommandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ClickPesaController extends Controller
{
    public function webhook(Request $request, ClickPesaService $clickPesaService, RobotArmCommandService $robot)
    {
        $payload = $request->all();

        Log::info('ClickPesa webhook received', [
            'payload' => $payload,
        ]);

        if (config('clickpesa.use_checksum') && !$clickPesaService->validChecksum($payload)) {
            Log::warning('Invalid ClickPesa webhook checksum', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid checksum'], 401);
        }

        $this->applyPaymentEvent($payload, $robot);

        return response()->json(['received' => true]);
    }

    public function callback(Request $request, RobotArmCommandService $robot)
    {
        $payload = $request->all();

        Log::info('ClickPesa callback received', [
            'payload' => $payload,
        ]);

        $this->applyPaymentEvent($payload, $robot);

        return response()->json(['received' => true]);
    }

    private function applyPaymentEvent(array $payload, RobotArmCommandService $robot): void
    {
        $event = strtoupper(trim((string) ($payload['event'] ?? $payload['eventType'] ?? $payload['type'] ?? '')));
        $data = $payload['data'] ?? $payload['payment'] ?? $payload;
        $data = is_array($data) ? $data : $payload;
        $orderReference = $this->firstValue($data, $payload, [
            'orderReference',
            'order_reference',
            'orderRef',
            'reference',
            'clientReference',
            'merchantReference',
            'externalReference',
        ]);

        $paymentId = $this->firstValue($data, $payload, [
            'id',
            'paymentId',
            'payment_id',
            'transactionId',
            'transaction_id',
        ]);

        $paymentReference = $this->firstValue($data, $payload, [
            'paymentReference',
            'payment_reference',
            'providerReference',
            'provider_reference',
            'receipt',
            'receiptNumber',
        ]);

        if (!$orderReference && !$paymentId && !$paymentReference) {
            Log::warning('ClickPesa event missing order lookup fields', ['payload' => $payload]);

            return;
        }

        $order = $this->findOrder($orderReference, $paymentId, $paymentReference);

        if (!$order) {
            Log::warning('ClickPesa event order not found', [
                'order_reference' => $orderReference,
                'payment_id' => $paymentId,
                'payment_reference' => $paymentReference,
                'payload' => $payload,
            ]);

            return;
        }

        $shouldDispatchRobot = DB::transaction(function () use ($order, $event, $data, $payload): bool {
            $wasConfirmed = $order->status === 'confirmed'
                && in_array(strtolower((string) $order->payment_status), ['paid', 'successful', 'success', 'completed'], true);

            $status = strtoupper(trim((string) ($data['status'] ?? $data['paymentStatus'] ?? $data['state'] ?? '')));
            $message = $data['message'] ?? $data['description'] ?? $data['statusDescription'] ?? $order->payment_message;

            $order->clickpesa_payload = $payload;
            $order->clickpesa_payment_id = $data['id'] ?? $data['paymentId'] ?? $data['payment_id'] ?? $order->clickpesa_payment_id;
            $order->clickpesa_payment_reference = $data['paymentReference'] ?? $data['payment_reference'] ?? $data['providerReference'] ?? $order->clickpesa_payment_reference;
            $order->clickpesa_channel = $data['channel'] ?? $order->clickpesa_channel;
            $order->payment_message = $message;

            if ($this->isSuccessfulPayment($event, $status)) {
                if ($order->stock_deducted_at === null) {
                    foreach ($order->orderItems as $item) {
                        if (!$item->product || $item->product->stock < $item->quantity) {
                            throw new RuntimeException("Insufficient stock for order {$order->order_number}");
                        }
                    }

                    foreach ($order->orderItems as $item) {
                        $item->product->decrement('stock', $item->quantity);
                    }

                    $order->stock_deducted_at = now();
                }

                $order->payment_status = 'paid';
                $order->status = 'confirmed';
                $order->paid_at = $order->paid_at ?: now();
            }

            if ($this->isFailedPayment($event, $status)) {
                $order->payment_status = 'failed';
                $order->payment_failed_at = $order->payment_failed_at ?: now();
            }

            if ($this->isPendingPayment($event, $status)) {
                $order->payment_status = strtolower($status);
            }

            $order->save();

            return !$wasConfirmed
                && $order->status === 'confirmed'
                && $order->payment_status === 'paid';
        });

        if ($shouldDispatchRobot) {
            try {
                $robot->dispatchPickForOrderIfNeeded($order->fresh(['orderItems.product']));
            } catch (Throwable $exception) {
                Log::error('Automatic robot PICK dispatch failed after ClickPesa confirmation', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function firstValue(array $data, array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? $payload[$key] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    private function findOrder(?string $orderReference, ?string $paymentId, ?string $paymentReference): ?Order
    {
        $order = Order::with('orderItems.product')
            ->where(function ($query) use ($orderReference, $paymentId, $paymentReference) {
                if ($orderReference) {
                    $cleanReference = preg_replace('/[^A-Za-z0-9]/', '', $orderReference);

                    $query->where('order_number', $orderReference)
                        ->orWhereRaw("REPLACE(order_number, '-', '') = ?", [$cleanReference])
                        ->orWhere('public_id', $orderReference)
                        ->orWhere('clickpesa_payload->orderReference', $orderReference)
                        ->orWhere('clickpesa_payload->orderReference', $cleanReference);
                }

                if ($paymentId) {
                    $query->orWhere('clickpesa_payment_id', $paymentId);
                }

                if ($paymentReference) {
                    $query->orWhere('clickpesa_payment_reference', $paymentReference);
                }
            })
            ->first();

        if ($order || !$orderReference) {
            return $order;
        }

        $cleanReference = preg_replace('/[^A-Za-z0-9]/', '', $orderReference);

        return Order::with('orderItems.product')
            ->latest('ordered_at')
            ->take(100)
            ->get()
            ->first(function (Order $order) use ($cleanReference): bool {
                $cleanOrderNumber = preg_replace('/[^A-Za-z0-9]/', '', (string) $order->order_number);

                return $cleanOrderNumber !== ''
                    && str_starts_with($cleanReference, $cleanOrderNumber . 'R');
            });
    }

    private function isSuccessfulPayment(string $event, string $status): bool
    {
        return in_array($event, ['PAYMENT RECEIVED', 'PAYMENT_SUCCESS', 'PAYMENT SUCCESS', 'PAYMENT COMPLETED'], true)
            || in_array($status, ['SUCCESS', 'SUCCESSFUL', 'PAID', 'COMPLETED', 'COMPLETE', 'SETTLED', 'APPROVED'], true);
    }

    private function isFailedPayment(string $event, string $status): bool
    {
        return in_array($event, ['PAYMENT FAILED', 'PAYMENT_FAILED', 'PAYMENT CANCELLED', 'PAYMENT CANCELED'], true)
            || in_array($status, ['FAILED', 'FAILURE', 'CANCELLED', 'CANCELED', 'REJECTED', 'EXPIRED', 'TIMEOUT'], true);
    }

    private function isPendingPayment(string $event, string $status): bool
    {
        return in_array($event, ['PAYMENT PROCESSING', 'PAYMENT PENDING'], true)
            || in_array($status, ['PROCESSING', 'PENDING', 'INITIATED', 'IN_PROGRESS'], true);
    }
}
