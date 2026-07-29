<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with('orderItems.product')
            ->orderBy('ordered_at', 'desc')
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    /**
     * Display specific order
     */
    public function show(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.product', 'orderAddresses']);

        return view('shop.order-detail', compact('order'));
    }

    /**
     * Cancel an order (if allowed)
     */
    public function cancel(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'This order cannot be cancelled at this stage.');
        }

        // Update order status
        $order->update(['status' => 'cancelled']);

        // Restore stock only if it had been deducted at confirmation.
        if ($order->stock_deducted_at !== null) {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
            $order->update(['stock_deducted_at' => null]);
        }

        return redirect()->back()
            ->with('success', 'Order cancelled successfully.');
    }
}
