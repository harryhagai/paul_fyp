<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display seller dashboard
     */
    public function index()
    {
        $seller = Auth::user();

        // Product Statistics (All products in system)
        $totalProducts = Product::count();
        $activeProducts = Product::where('stock', '>', 0)->count();
        $advertisedProducts = Product::where('is_advertised', true)->count();
        $totalStock = Product::sum('stock');

        // Order Statistics (All orders in system)
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        // Recent Orders (last 3 - all orders)
        $recentOrders = Order::with(['orderItems.product', 'user'])
            ->orderBy('ordered_at', 'desc')
            ->take(3)
            ->get();

        // Top Products (most sold products in system)
        $topProducts = Product::withCount(['orderItems as sold_count' => function ($query) {
                $query->join('orders', 'order_items.order_id', '=', 'orders.id')
                      ->where('orders.status', 'completed');
            }])
            ->orderBy('sold_count', 'desc')
            ->take(4)
            ->get();

        // Monthly Revenue Trend (last 6 months - all revenue)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'amount' => Order::where('status', 'completed')
                    ->whereYear('ordered_at', $date->year)
                    ->whereMonth('ordered_at', $date->month)
                    ->sum('total_amount')
            ];
        }

        // Recent Activity (all recent orders)
        $recentActivity = collect();

        // Recent order activity
        $recentOrderActivity = Order::select('id', 'public_id', 'order_number', 'ordered_at', 'status', 'total_amount')
            ->orderBy('ordered_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($order) {
                $orderRef = $order->order_number ?: $order->public_id;
                return [
                    'type' => 'order',
                    'message' => "New order {$orderRef} received",
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'date' => $order->ordered_at,
                ];
            });

        $recentActivity = $recentActivity->merge($recentOrderActivity)->sortByDesc('date')->take(5);

        // Low Stock Products (all products with low stock)
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->take(3)
            ->get();

        // Stock Status Data for Chart - Historical data for last 7 days
        $stockChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('M d');

            // Count products in each stock category for this date
            // For simplicity, we'll use current data since we don't have historical stock tracking
            // In a real application, you'd query historical data from a stock_log table
            $outOfStock = Product::where('stock', '=', 0)->count();
            $lowStock = Product::where('stock', '>', 0)->where('stock', '<=', 10)->count();
            $mediumStock = Product::where('stock', '>', 10)->where('stock', '<=', 50)->count();
            $highStock = Product::where('stock', '>', 50)->count();

            $stockChartData[] = [
                'date' => $dateKey,
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
                'medium_stock' => $mediumStock,
                'high_stock' => $highStock,
            ];
        }

        // Current Stock Status Data for Legend
        $stockStatusData = [
            'out_of_stock' => Product::where('stock', '=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'medium_stock' => Product::where('stock', '>', 10)->where('stock', '<=', 50)->count(),
            'high_stock' => Product::where('stock', '>', 50)->count(),
        ];

        // Category Breakdown - Products by Category
        $categoryBreakdown = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->products_count,
                    'description' => $category->description,
                ];
            });

        // Quick stats for tiles
        $stats = [
            [
                'title' => 'Total Products',
                'value' => $totalProducts,
                'icon' => 'bi-box-seam',
                'color' => 'primary',
                'change' => '+5%',
                'change_type' => 'increase'
            ],
            [
                'title' => 'Total Revenue',
                'value' => 'Tsh ' . number_format($totalRevenue, 0),
                'icon' => 'bi-cash-stack',
                'color' => 'success',
                'change' => '+12%',
                'change_type' => 'increase'
            ],
            [
                'title' => 'Active Orders',
                'value' => $pendingOrders,
                'icon' => 'bi-clock-history',
                'color' => 'warning',
                'change' => $pendingOrders > 0 ? 'Pending' : 'Clear',
                'change_type' => 'neutral'
            ],
            [
                'title' => 'Total Stock',
                'value' => $totalStock,
                'icon' => 'bi-diagram-3',
                'color' => 'info',
                'change' => '+8%',
                'change_type' => 'increase'
            ]
        ];

        return view('seller.dashboard', compact(
            'stats',
            'totalProducts',
            'activeProducts',
            'advertisedProducts',
            'totalStock',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalRevenue',
            'recentOrders',
            'topProducts',
            'monthlyRevenue',
            'recentActivity',
            'lowStockProducts',
            'stockStatusData',
            'stockChartData',
            'categoryBreakdown'
        ));
    }
}
