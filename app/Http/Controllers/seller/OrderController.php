<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->autoCancelExpiredPendingOrders();

        $query = Order::with(['user', 'orderItems.product']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('public_id', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(5);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('seller.partials.order_rows', compact('orders'))->render(),
                'next_page_url' => $orders->nextPageUrl(),
            ]);
        }

        return view('seller.orders', compact('orders'));
    }

    public function show($id)
    {
        $this->autoCancelExpiredPendingOrders();

        try {
            $order = Order::with(['user', 'orderItems.product'])
                ->wherePublicIdOrId($id)
                ->firstOrFail();

            // Format order items safely
            $formattedOrderItems = $order->orderItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'product' => $item->product ? [
                        'name' => $item->product->name ?: 'Unknown Product'
                    ] : ['name' => 'Product Not Found'],
                    'quantity' => $item->quantity ?? 0,
                    'price' => $item->price ?? 0,
                    'total' => ($item->price ?? 0) * ($item->quantity ?? 0)
                ];
            });

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                'user' => $order->user ? [
                    'first_name' => $order->user->name,
                    'last_name' => '',
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone_number ?? ''
                ] : ['name' => 'Unknown', 'email' => 'unknown@example.com', 'phone' => ''],
                    'order_items' => $formattedOrderItems->toArray(),
                    'total_amount' => $order->total_amount ?? 0,
                    'subtotal' => $order->subtotal ?? $order->total_amount ?? 0,
                    'status' => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? 'pending',
                    'created_at' => $order->created_at,
                    'notes' => $order->notes ?? '',
                    'order_address' => null // Temporarily set to null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading order details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $this->autoCancelExpiredPendingOrders();

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $order = Order::with('orderItems.product')
            ->wherePublicIdOrId($id)
            ->firstOrFail();
        $currentStatus = $order->status;
        $newStatus = $request->status;

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid status transition from {$currentStatus} to {$newStatus}.",
            ], 422);
        }

        if ($newStatus === 'confirmed' && $order->stock_deducted_at === null) {
            foreach ($order->orderItems as $item) {
                if (!$item->product || $item->product->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$item->product->name}.",
                    ], 422);
                }
            }
        }

        DB::transaction(function () use ($order, $newStatus) {
            if ($newStatus === 'confirmed' && $order->stock_deducted_at === null) {
                foreach ($order->orderItems as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }

                $order->stock_deducted_at = now();
            }

            if ($newStatus === 'cancelled' && $order->stock_deducted_at !== null) {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                $order->stock_deducted_at = null;
            }

            $order->status = $newStatus;
            $order->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }

    private function autoCancelExpiredPendingOrders(): void
    {
        $rawHours = SiteSetting::where('key', 'order_auto_cancel_hours')->value('value');
        $rawMinutes = SiteSetting::where('key', 'order_auto_cancel_minutes')->value('value');
        $rawSeconds = SiteSetting::where('key', 'order_auto_cancel_seconds')->value('value');

        $allMissing = ($rawHours === null || $rawHours === '')
            && ($rawMinutes === null || $rawMinutes === '')
            && ($rawSeconds === null || $rawSeconds === '');

        $hours = (int) ($rawHours === null || $rawHours === '' ? 0 : $rawHours);
        $minutes = (int) ($rawMinutes === null || $rawMinutes === '' ? 0 : $rawMinutes);
        $seconds = (int) ($rawSeconds === null || $rawSeconds === '' ? 0 : $rawSeconds);

        $hours = max(0, min(720, $hours));
        $minutes = max(0, min(59, $minutes));
        $seconds = max(0, min(59, $seconds));

        $ttlSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        if ($allMissing || $ttlSeconds <= 0) {
            $ttlSeconds = 24 * 3600;
        }

        $cutoff = now()->subSeconds($ttlSeconds);
        $expiredOrders = Order::with('orderItems.product')
            ->where('status', 'pending')
            ->where('ordered_at', '<=', $cutoff)
            ->get();

        if ($expiredOrders->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($expiredOrders) {
            foreach ($expiredOrders as $order) {
                if ($order->stock_deducted_at !== null) {
                    foreach ($order->orderItems as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }

                $order->status = 'cancelled';
                $order->stock_deducted_at = null;
                $order->save();
            }
        });
    }
}
