<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $completedStatuses = ['complete', 'completed'];

        // Order Statistics
        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('status', 'pending')->count();
        $completedOrders = Order::where('user_id', $user->id)->whereIn('status', $completedStatuses)->count();
        $cancelledOrders = Order::where('user_id', $user->id)->where('status', 'cancelled')->count();
        $totalSpent = Order::where('user_id', $user->id)->whereIn('status', $completedStatuses)->sum('total_amount');

        // Cart Statistics
        $cart = Cart::where('user_id', $user->id)->first();
        $cartItemsCount = $cart ? $cart->cartItems->sum('quantity') : 0;
        $cartTotal = $cart ? $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        }) : 0;

        // Recent Orders (last 2)
        $recentOrders = Order::where('user_id', $user->id)
            ->with('orderItems.product')
            ->orderBy('ordered_at', 'desc')
            ->take(2)
            ->get();

        // Monthly spending trend (last 6 months)
        $monthlySpending = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlySpending[] = [
                'month' => $date->format('M Y'),
                'amount' => (float) Order::where('user_id', $user->id)
                    ->whereIn('status', $completedStatuses)
                    ->whereYear('ordered_at', $date->year)
                    ->whereMonth('ordered_at', $date->month)
                    ->sum('total_amount'),
            ];
        }

        // Recent confirmed/completed orders activity (last 5)
        $recentActivity = Order::where('user_id', $user->id)
            ->whereIn('status', $completedStatuses)
            ->select('id', 'public_id', 'order_number', 'ordered_at', 'status', 'total_amount')
            ->orderBy('ordered_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                $orderRef = $order->order_number ?: $order->public_id;
                return [
                    'type' => 'order',
                    'message' => "Order {$orderRef} confirmed",
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'date' => $order->ordered_at,
                ];
            });

        // Quick stats for tiles
        $stats = [
            [
                'title' => 'Total Orders',
                'value' => $totalOrders,
                'icon' => 'bi-receipt',
                'color' => 'primary',
                'change' => '+12%',
                'change_type' => 'increase'
            ],
            [
                'title' => 'Total Spent',
                'value' => 'Tsh ' . number_format($totalSpent, 0),
                'icon' => 'bi-cash-stack',
                'color' => 'success',
                'change' => '+8%',
                'change_type' => 'increase'
            ],
            [
                'title' => 'Cart Items',
                'value' => $cartItemsCount,
                'icon' => 'bi-cart-fill',
                'color' => 'warning',
                'change' => $cartItemsCount > 0 ? 'Active' : 'Empty',
                'change_type' => 'neutral'
            ],
        ];

        return view('customer.dashboard', compact(
            'user',
            'stats',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalSpent',
            'cartItemsCount',
            'cartTotal',
            'recentOrders',
            'monthlySpending',
            'recentActivity'
        ));
    }
}
