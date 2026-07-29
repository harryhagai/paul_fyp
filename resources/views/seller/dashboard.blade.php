@extends('layouts.dashboard')

@section('title', 'Seller Dashboard - KidsStore')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/seller-dashboard-clean.css') }}">
@endsection

@section('content')
    <div class="container-fluid mt-2 seller-dashboard-clean">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 dashboard-title"><i class="bi bi-speedometer2 me-2"></i>Seller Dashboard</h1>
                <p class="dashboard-subtitle mb-0">Track sales, orders, stock, and performance insights in one place.</p>
            </div>
            <a href="{{ route('seller.products') }}" class="btn dashboard-primary-btn">
                <i class="bi bi-grid me-1"></i>Manage Products
            </a>
        </div>

        @php
            $lowStockCount = $lowStockProducts->count();
            $maxCategoryCount = max(1, (int) $categoryBreakdown->max('count'));
            $trendLabels = [];
            $trendOrders = [];
            $revenueLabels = collect($monthlyRevenue)->pluck('month')->values();
            $revenueAmounts = collect($monthlyRevenue)->map(fn($month) => (float) ($month['amount'] ?? 0))->values();
            $hasRevenueData = $revenueLabels->isNotEmpty();
            $topProductChartItems = collect($topProducts)
                ->filter(fn($product) => (int) ($product->sold_count ?? 0) > 0)
                ->take(8)
                ->values();
            $topProductLabels = $topProductChartItems
                ->map(fn($product) => \Illuminate\Support\Str::limit((string) $product->name, 28, '...'))
                ->values();
            $topProductSales = $topProductChartItems->map(fn($product) => (int) ($product->sold_count ?? 0))->values();
            $hasTopProductData = $topProductSales->sum() > 0;
            $maxRevenue = (float) $revenueAmounts->max();
            $revenueStep =
                $maxRevenue <= 1000
                    ? 250
                    : ($maxRevenue <= 5000
                        ? 1000
                        : ($maxRevenue <= 20000
                            ? 5000
                            : ($maxRevenue <= 100000
                                ? 10000
                                : 25000)));
            $revenueAxisMax = max($revenueStep, (int) ceil(($maxRevenue + $revenueStep) / $revenueStep) * $revenueStep);
            $revenueAxisLabelMap = [];
            for ($tick = 0; $tick <= $revenueAxisMax; $tick += $revenueStep) {
                $revenueAxisLabelMap[(string) $tick] = format_money_short($tick, 1);
            }
            $revenuePointLabels = $revenueAmounts->map(fn($amount) => format_money_short($amount, 1))->values();
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $trendLabels[] = $day->format('D');
                $trendOrders[] = \App\Models\Order::whereDate('ordered_at', $day->toDateString())->count();
            }
            $hasTrendData = array_sum($trendOrders) > 0;
            $trendCount = count($trendOrders);
            $latestTrendOrders = $trendCount > 0 ? (int) $trendOrders[$trendCount - 1] : 0;
            $previousTrendOrders = $trendCount > 1 ? (int) $trendOrders[$trendCount - 2] : 0;
            $trendDeltaPct =
                $previousTrendOrders > 0
                    ? round((($latestTrendOrders - $previousTrendOrders) / $previousTrendOrders) * 100, 1)
                    : null;
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="summary-card">
                    <i class="bi bi-cash-coin summary-watermark"></i>
                    <div class="summary-label">Total Revenue</div>
                    <div class="summary-value" data-counter-target="{{ (float) $totalRevenue }}" data-counter-type="money"
                        data-counter-currency="Tsh" data-counter-precision="0">{{ format_money_short($totalRevenue, 0) }}
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('seller.orders') }}" class="summary-card summary-link" aria-label="View total orders">
                    <i class="bi bi-bag-check summary-watermark"></i>
                    <div class="summary-label">Total Orders</div>
                    <div class="summary-value" data-counter-target="{{ (int) $totalOrders }}" data-counter-type="number">
                        {{ number_format($totalOrders) }}</div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('seller.orders', ['status' => 'pending']) }}" class="summary-card summary-link"
                    aria-label="View pending orders">
                    <i class="bi bi-hourglass-split summary-watermark"></i>
                    <div class="summary-label">Pending Orders</div>
                    <div class="summary-value" data-counter-target="{{ (int) $pendingOrders }}" data-counter-type="number">
                        {{ number_format($pendingOrders) }}</div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('seller.products') }}" class="summary-card summary-link"
                    aria-label="View low stock products">
                    <i class="bi bi-exclamation-triangle summary-watermark"></i>
                    <div class="summary-label">Low Stock Items</div>
                    <div class="summary-value" data-counter-target="{{ (int) $lowStockCount }}" data-counter-type="number">
                        {{ number_format($lowStockCount) }}</div>
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                        <a href="{{ route('seller.orders') }}" class="btn btn-sm dashboard-primary-btn">
                            <i class="bi bi-list-ul me-1"></i>View all
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentOrders->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Order</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Amount</th>
                                            <th class="pe-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentOrders as $order)
                                            <tr>
                                                <td class="ps-3 font-monospace">
                                                    {{ $order->order_number ?: $order->public_id }}</td>
                                                <td>{{ optional($order->ordered_at)->format('M d, Y') }}</td>
                                                <td>{{ $order->orderItems->count() }}</td>
                                                <td>{{ format_money_short($order->total_amount, 0) }}</td>
                                                <td class="pe-3">
                                                    @php
                                                        $statusClass = match (strtolower((string) $order->status)) {
                                                            'pending' => 'status-badge-soft-warning',
                                                            'processing', 'confirmed' => 'status-badge-soft-info',
                                                            'preparing',
                                                            'ready_for_pickup',
                                                            'shipped'
                                                                => 'status-badge-soft-primary',
                                                            'completed', 'delivered' => 'status-badge-soft-success',
                                                            'cancelled', 'canceled' => 'status-badge-soft-danger',
                                                            default => 'status-badge-soft-neutral',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge text-capitalize border {{ $statusClass }}">{{ str_replace('_', ' ', $order->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="dashboard-empty-state p-4">
                                <i class="bi bi-receipt-cutoff"></i>
                                <p class="mb-0">No recent orders yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card clean-card trend-card h-100">
                    <div class="card-header clean-card-header trend-card-header">
                        <div>
                            <p class="trend-kicker mb-1">Last 7 Days</p>
                            <h5 class="mb-0 trend-title">Orders Trend (Last 7 Days)</h5>
                        </div>
                        <div class="trend-stat">
                            @if (!is_null($trendDeltaPct))
                                <span class="trend-stat-badge {{ $trendDeltaPct >= 0 ? 'up' : 'down' }}">
                                    <i
                                        class="bi {{ $trendDeltaPct >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $trendDeltaPct > 0 ? '+' : '' }}{{ number_format($trendDeltaPct, 1) }}%
                                </span>
                                <small class="trend-stat-label">vs previous day</small>
                            @else
                                <span class="trend-stat-badge neutral">{{ number_format($latestTrendOrders) }}</span>
                                <small class="trend-stat-label">orders today</small>
                            @endif
                        </div>
                    </div>
                    <div class="card-body trend-card-body">
                        @if ($hasTrendData)
                            <div class="trend-chart-wrap">
                                <canvas id="ordersTrendChart"></canvas>
                            </div>
                        @else
                            <div class="dashboard-empty-state">
                                <i class="bi bi-graph-up-arrow"></i>
                                <p class="mb-0">No order trend data in the last 7 days.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
                    </div>
                    <div class="card-body">
                        @if ($hasTopProductData)
                            <div class="top-products-chart-wrap">
                                <canvas id="topSellingProductsChart"></canvas>
                            </div>
                        @else
                            <div class="dashboard-empty-state">
                                <i class="bi bi-box-seam"></i>
                                <p class="mb-0">No sales data yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Category Summary</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $categorySummaryItems = collect($categoryBreakdown)->filter(
                                fn($category) => (int) ($category['count'] ?? 0) > 0,
                            );
                        @endphp
                        @forelse($categorySummaryItems as $category)
                            @php
                                $pct = round(($category['count'] / $maxCategoryCount) * 100);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $category['name'] }}</span>
                                    <span class="text-muted">{{ $category['count'] }}</span>
                                </div>
                                <div class="progress clean-progress">
                                    <div class="progress-bar category-summary-bar"
                                        data-target-width="{{ $pct }}" style="width: 0%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty-state">
                                <i class="bi bi-tags"></i>
                                <p class="mb-0">No category data available.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Revenue (Last 6 Months)</h5>
                    </div>
                    <div class="card-body">
                        @if ($hasRevenueData)
                            <div class="revenue-chart-wrap">
                                <canvas id="revenueTrendChart"></canvas>
                            </div>
                        @else
                            <div class="dashboard-empty-state">
                                <i class="bi bi-cash-stack"></i>
                                <p class="mb-0">No revenue data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentActivity as $activity)
                            <div class="list-row">
                                <div>
                                    <div>{{ $activity['message'] }}</div>
                                    <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                </div>
                                @if (isset($activity['amount']))
                                    <span class="text-muted">{{ format_money_short($activity['amount'], 0) }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="dashboard-empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p class="mb-0">No recent activity.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script id="sellerDashboardChartsData" type="application/json">
{!! json_encode([
    'trendLabels' => $trendLabels,
    'trendOrders' => $trendOrders,
    'revenueLabels' => $revenueLabels,
    'revenueAmounts' => $revenueAmounts,
    'revenuePointLabels' => $revenuePointLabels,
    'revenueAxisLabelMap' => $revenueAxisLabelMap,
    'revenueStep' => $revenueStep,
    'revenueAxisMax' => $revenueAxisMax,
    'topProductLabels' => $topProductLabels,
    'topProductSales' => $topProductSales,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
    <script src="{{ asset('js/seller-dashboard-charts.js') }}"></script>
@endsection
