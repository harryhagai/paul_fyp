<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ClickPesaOrderSyncService;
use App\Services\ClickPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get order statistics
        $totalOrders = Order::query()->forUser($user->id)->count('*');
        $pendingOrders = Order::query()->forUser($user->id)->byStatus('pending')->count('*');
        $completedOrders = Order::query()->forUser($user->id)->byStatus('completed')->count('*');
        $totalSpent = Order::query()->forUser($user->id)->sum('total_amount');

        // Get recent orders
        $recentOrders = Order::query()->forUser($user->id)
            ->with('orderItems')
            ->orderBy('ordered_at', 'desc')
            ->take(5)
            ->get();

        // Get cart items count
        $cartItemsCount = DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->where('carts.user_id', $user->id)
            ->sum('cart_items.quantity');

        // Get featured products (you can customize this logic)
        $featuredProducts = Product::query()->whereRaw('stock > ?', [0], 'and')
            ->where('is_advertised', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('customer.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalSpent',
            'recentOrders',
            'cartItemsCount',
            'featuredProducts'
        ));
    }

    /**
     * Display customer orders
     */
    public function orders(Request $request)
    {
        $user = Auth::user();

        app(ClickPesaOrderSyncService::class)->syncRecentProcessingOrdersForUser($user->id);

        $query = Order::query()->forUser($user->id)
            ->with('orderItems.product.media')
            ->orderBy('ordered_at', 'desc');

        // Search by order code/number, status, or product name
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('public_id', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('orderItems.product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('customer.partials.order_rows', compact('orders'))->render(),
                'next_page_url' => $orders->nextPageUrl(),
                'from' => $orders->firstItem() ?? 0,
                'to' => $orders->lastItem() ?? 0,
                'total' => $orders->total(),
            ]);
        }

        return view('customer.orders', compact('orders'));
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        // Update order status to cancelled
        $order->update(['status' => 'cancelled']);

        if ($order->stock_deducted_at !== null) {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity, []);
                }
            }
            $order->update(['stock_deducted_at' => null]);
        }

        return back()->with('success', 'Order cancelled successfully.');
    }

    /**
     * Send a new mobile money payment prompt for a pending order.
     */
    public function payOrder(Order $order, ClickPesaService $clickPesaService)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        app(ClickPesaOrderSyncService::class)->syncOrder($order);
        $order->refresh();

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This order can no longer be paid from here because it is already ' . $order->status_text . '.',
            ], 422);
        }

        if (in_array(strtolower((string) $order->payment_status), ['paid', 'successful', 'success', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
            ], 422);
        }

        $paymentPhone = $order->customer_phone ?: $order->user?->phone_number;

        if (!$paymentPhone) {
            return response()->json([
                'success' => false,
                'message' => 'We do not have a phone number for this order. Please contact support or place the order again with a mobile money number.',
            ], 422);
        }

        try {
            $paymentOrderReference = $this->paymentRetryReference($order, $clickPesaService);
            $payment = $clickPesaService->initiateUssdPush($order->fresh('user'), $paymentOrderReference);

            $order->update([
                'payment_status' => strtolower($payment['status'] ?? 'processing'),
                'clickpesa_payment_id' => $payment['id'] ?? $order->clickpesa_payment_id,
                'clickpesa_client_id' => $payment['clientId'] ?? $order->clickpesa_client_id,
                'clickpesa_channel' => $payment['channel'] ?? $order->clickpesa_channel,
                'clickpesa_payload' => array_merge($payment, [
                    'orderReference' => $paymentOrderReference,
                    'retry_for_order_number' => $order->order_number,
                ]),
                'payment_message' => $payment['message'] ?? 'Payment request sent to customer phone.',
                'payment_failed_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'A mobile money payment request has been sent to ' . $paymentPhone . '. Please check your phone, enter your PIN, and confirm the payment for order ' . ($order->order_number ?: $order->public_id) . '.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Customer payment retry failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->friendlyPaymentPromptFailureMessage($e),
            ], 502);
        }
    }

    /**
     * Update order item quantity
     */
    public function updateOrderItem(Request $request, Order $order, OrderItem $orderItem)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Ensure order item belongs to this order
        if ($orderItem->order_id !== $order->id) {
            abort(403);
        }

        // Check if order can be updated
        if (!$order->canBeUpdated()) {
            return response()->json(['error' => 'This order cannot be updated.'], 403);
        }

        $product = $orderItem->product;
        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'This product is no longer available.',
            ], 422);
        }

        $maxAllowed = max((int) $product->stock, 1);
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $maxAllowed,
        ]);

        $quantity = $request->quantity;

        // Update the order item
        $orderItem->update([
            'quantity' => $quantity,
            'total_price' => $orderItem->price * $quantity,
        ]);

        // Recalculate order totals
        $this->recalculateOrderTotals($order);

        return response()->json([
            'success' => true,
            'item_total' => format_money_short($orderItem->total_price, 2),
            'order_total' => format_money_short($order->total_amount, 2),
        ]);
    }

    /**
     * Remove order item
     */
    public function removeOrderItem(Order $order, OrderItem $orderItem)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Ensure order item belongs to this order
        if ($orderItem->order_id !== $order->id) {
            abort(403);
        }

        $expectsJson = request()->expectsJson() || request()->ajax() || request()->wantsJson();

        // Check if order can be updated
        if (!$order->canBeUpdated()) {
            if ($expectsJson) {
                return response()->json(['success' => false, 'message' => 'This order cannot be updated.'], 403);
            }
            return back()->with('error', 'This order cannot be updated.');
        }

        // Delete the order item
        OrderItem::destroy($orderItem->id);

        // If no items left, cancel the order
        if ($order->orderItems()->count() === 0) {
            $order->update([
                'status' => 'cancelled',
                'subtotal' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ]);
            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled as no items remained.',
                    'order_total' => format_money_short(0, 2),
                ]);
            }
            return back()->with('success', 'Order cancelled as no items remained.');
        }

        // Recalculate order totals
        $this->recalculateOrderTotals($order);

        if ($expectsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from order successfully.',
                'order_total' => format_money_short($order->total_amount, 2)
            ]);
        }
        return back()->with('success', 'Item removed from order successfully.');
    }

    /**
     * Recalculate order totals
     */
    private function recalculateOrderTotals(Order $order)
    {
        $subtotal = $order->orderItems->sum('total_price');
        $totalAmount = $subtotal - $order->discount_amount;

        $order->update([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
        ]);
    }

    private function friendlyPaymentPromptFailureMessage(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'amount must be between')) {
            return 'We could not send the mobile money request because the order amount is outside the allowed mobile money limit. Please adjust your order or contact support.';
        }

        if (str_contains($message, 'reference') && str_contains($message, 'already used')) {
            return 'We could not send another payment request for this order right now. Please wait a moment and try again.';
        }

        if (str_contains($message, 'order reference') && str_contains($message, '20 characters')) {
            return 'We could not send the payment request right now. Please try again.';
        }

        if (str_contains($message, 'phone') || str_contains($message, 'msisdn')) {
            return 'We could not send the mobile money request. Please check that the phone number on this order is correct and active for mobile money.';
        }

        if (str_contains($message, 'credentials') || str_contains($message, 'token')) {
            return 'We could not start the payment right now. Please try again in a few minutes.';
        }

        return 'We could not send the mobile money request to your phone. Please try again.';
    }

    private function paymentRetryReference(Order $order, ClickPesaService $clickPesaService): string
    {
        $baseReference = $clickPesaService->orderReference($order);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $suffix = 'R' . Str::upper(Str::random(4));
            $reference = substr($baseReference, 0, 20 - strlen($suffix)) . $suffix;

            if (!Order::where('clickpesa_payload->orderReference', $reference)->exists()) {
                return $reference;
            }
        }

        $suffix = 'R' . Str::upper(Str::random(4));

        return substr($baseReference, 0, 20 - strlen($suffix)) . $suffix;
    }

    /**
     * Display order details
     */
    public function orderDetails(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        app(ClickPesaOrderSyncService::class)->syncOrder($order);
        $order->refresh();
        $order->load('orderItems.product.media', 'orderAddresses');

        // Recalculate totals if subtotal is 0 (for existing orders)
        if ($order->subtotal == 0 && $order->orderItems->count() > 0) {
            $this->recalculateOrderTotals($order);
            // Reload the order with updated totals
            $order->refresh();
        }

        return view('customer.order-details', compact('order'));
    }

    /**
     * Display customer profile
     */
    public function profile()
    {
        $user = Auth::user();

        return view('customer.profile', compact('user'));
    }

    /**
     * Display addresses management
     */
    public function addresses()
    {
        $user = Auth::user();

        // For now, return empty addresses until we implement addresses functionality
        $addresses = collect();

        return view('customer.addresses', compact('addresses'));
    }

}
