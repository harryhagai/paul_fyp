


<?php $__env->startSection('styles'); ?>
    <link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/admin-dashboard.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $today = \Carbon\Carbon::today();

        $totalUsers = \App\Models\User::count();
        $totalProducts = \App\Models\Product::count();
        $totalOrders = \App\Models\Order::count();
        $totalSettings = \App\Models\SiteSetting::count();

        $orders24h = \App\Models\Order::where('created_at', '>=', now()->subHours(24))->count();
        $users24h = \App\Models\User::where('created_at', '>=', now()->subHours(24))->count();
        $products24h = \App\Models\Product::where('created_at', '>=', now()->subHours(24))->count();

        $labels = [];
        $ordersSeries = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $labels[] = $day->format('M d');
            $ordersSeries[] = \App\Models\Order::whereDate('created_at', $day->toDateString())->count();
        }

        $healthScore = min(
            100,
            max(
                0,
                (int) round(
                    ($totalSettings > 20 ? 40 : $totalSettings * 2) +
                        ($orders24h > 0 ? 30 : 10) +
                        ($products24h > 0 ? 20 : 10) +
                        10,
                ),
            ),
        );
        $uptimePct = $orders24h > 0 ? 99.9 : 98.7;

        $recentOrders = \App\Models\Order::latest()
            ->take(6)
            ->get(['id', 'created_at']);
    ?>

    <div class="container-fluid admin-shell admin-shell-fit">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-hdd-stack me-3"></i>System Operations Dashboard</h1>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="panel-card p-3 h-100">
                    <div class="ops-kpi-label">Orders (24h)</div>
                    <div class="ops-kpi-value"><?php echo e(number_format($orders24h)); ?></div>
                    <div class="ops-kpi-sub">Total orders: <?php echo e(number_format($totalOrders)); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card p-3 h-100">
                    <div class="ops-kpi-label">New Users (24h)</div>
                    <div class="ops-kpi-value"><?php echo e(number_format($users24h)); ?></div>
                    <div class="ops-kpi-sub">Total users: <?php echo e(number_format($totalUsers)); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card p-3 h-100">
                    <div class="ops-kpi-label">Catalog Updates (24h)</div>
                    <div class="ops-kpi-value"><?php echo e(number_format($products24h)); ?></div>
                    <div class="ops-kpi-sub">Total products: <?php echo e(number_format($totalProducts)); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="panel-card p-3 h-100">
                    <div class="ops-kpi-label">Config Records</div>
                    <div class="ops-kpi-value"><?php echo e(number_format($totalSettings)); ?></div>
                    <div class="ops-kpi-sub">Platform settings keys</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="panel-card h-100">
                    <div class="panel-card-head d-flex justify-content-between align-items-center">
                        <h6 class="ops-section-title">Order Activity Trend (30 Days)</h6>
                        <span class="ops-badge ops-neutral">Operational Signal</span>
                    </div>
                    <div class="panel-card-body">
                        <div class="ops-chart-wrap">
                            <canvas id="ordersTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-card-head">
                        <h6 class="ops-section-title">System Health</h6>
                    </div>
                    <div class="panel-card-body d-grid gap-2">
                        <div class="ops-status d-flex justify-content-between align-items-center">
                            <div>
                                <div class="ops-status-head">Platform Uptime</div>
                                <div class="ops-status-value"><?php echo e($uptimePct); ?>%</div>
                            </div>
                            <span
                                class="ops-badge <?php echo e($uptimePct >= 99.5 ? 'ops-ok' : 'ops-warn'); ?>"><?php echo e($uptimePct >= 99.5 ? 'Stable' : 'Watch'); ?></span>
                        </div>
                        <div class="ops-status d-flex justify-content-between align-items-center">
                            <div>
                                <div class="ops-status-head">Health Score</div>
                                <div class="ops-status-value"><?php echo e($healthScore); ?>/100</div>
                            </div>
                            <span
                                class="ops-badge <?php echo e($healthScore >= 80 ? 'ops-ok' : 'ops-warn'); ?>"><?php echo e($healthScore >= 80 ? 'Good' : 'Attention'); ?></span>
                        </div>
                        <div class="ops-status d-flex justify-content-between align-items-center">
                            <div>
                                <div class="ops-status-head">Traffic Status</div>
                                <div class="ops-status-value"><?php echo e($orders24h > 0 ? 'Active' : 'Low'); ?></div>
                            </div>
                            <span class="ops-badge <?php echo e($orders24h > 0 ? 'ops-ok' : 'ops-neutral'); ?>">24h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="panel-card">
                    <div class="panel-card-head d-flex justify-content-between align-items-center">
                        <h6 class="ops-section-title">Recent Order Events</h6>
                        <a href="<?php echo e(route('admin.settings.header')); ?>"
                            class="btn btn-sm btn-outline-secondary">Header Settings</a>
                    </div>
                    <div class="panel-card-body p-0">
                        <div class="table-responsive">
                            <table class="table ops-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Event</th>
                                        <th>Reference</th>
                                        <th class="text-end pe-3">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="ps-3">Order created</td>
                                            <td class="font-monospace"><?php echo e($order->order_number ?: $order->public_id); ?></td>
                                            <td class="text-end pe-3 text-muted">
                                                <?php echo e($order->created_at->format('Y-m-d H:i')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No recent order events.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('ordersTrendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels, 15, 512) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode($ordersSeries, 15, 512) ?>,
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15,118,110,0.08)',
                    fill: true,
                    borderWidth: 2,
                    tension: 0.32,
                    pointRadius: 0,
                    pointHoverRadius: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#64748b',
                            precision: 0
                        },
                        grid: {
                            color: '#e2e8f0'
                        }
                    }
                }
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>