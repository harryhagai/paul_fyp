


<?php $__env->startSection('title', 'Customer Dashboard - KidsStore'); ?>

<?php $__env->startSection('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/customer-dashboard-clean.css')); ?>?v=<?php echo e(filemtime(public_path('css/customer-dashboard-clean.css'))); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid mt-2 customer-dashboard-clean">
        <div class="d-flex flex-column flex-md-row justify-content-start align-items-start align-items-md-center gap-2 gap-md-3 mb-4 dashboard-heading">
            <div>
                <h1 class="h3 mb-1 dashboard-title"><i class="bi bi-speedometer2 me-2"></i>My Dashboard</h1>
                <p class="dashboard-subtitle mb-0">Track orders, cart, and shopping activity in one place.</p>
            </div>
            <div class="dashboard-cta">
                <a href="<?php echo e(route('shop')); ?>" class="btn dashboard-primary-btn w-100">
                    <i class="bi bi-shop me-1"></i>Browse Products
                </a>
            </div>
        </div>

        <?php
            $recentOrders = $recentOrders ?? collect();
            $recentActivity = $recentActivity ?? collect();
            $profileUser = $user ?? auth()->user();
            $monthlySpending = collect($monthlySpending ?? [])->values();
            $monthlySpendingChart = $monthlySpending
                ->map(function ($item) {
                    $amount = (float) data_get($item, 'amount', 0);
                    return [
                        'month' => data_get($item, 'month'),
                        'amount' => $amount,
                        'amount_label' => format_money_short($amount, 0),
                    ];
                })
                ->values();
            $maxMonthlyAmount = (float) $monthlySpendingChart->max('amount');
            $tickSections = 5;
            $rawStep = $maxMonthlyAmount > 0 ? ($maxMonthlyAmount / $tickSections) : 1;
            $base = $rawStep > 0 ? pow(10, floor(log10($rawStep))) : 1;
            $yAxisStep = max(1, ceil($rawStep / $base) * $base);
            $yAxisIntervals = max($tickSections, (int) ceil($maxMonthlyAmount / $yAxisStep) + 1);
            $yAxisMax = $yAxisStep * $yAxisIntervals;
            $yAxisLabels = collect(range(0, $yAxisIntervals))
                ->mapWithKeys(function ($i) use ($yAxisStep) {
                    $value = (float) ($i * $yAxisStep);
                    return [(string) $value => format_money_short($value, 0)];
                });
            $yAxisConfig = [
                'max' => $yAxisMax,
                'step' => $yAxisStep,
                'labels' => $yAxisLabels,
            ];
            $completedOrders = (int) ($completedOrders ?? 0);
            $pendingOrders = (int) ($pendingOrders ?? 0);
            $cancelledOrders = (int) ($cancelledOrders ?? 0);
            $ordersTotalForChart = max(1, $completedOrders + $pendingOrders + $cancelledOrders);
            $completedDeg = round(($completedOrders / $ordersTotalForChart) * 360, 1);
            $pendingDeg = round(($pendingOrders / $ordersTotalForChart) * 360, 1);
            $cancelledDeg = max(0, 360 - $completedDeg - $pendingDeg);
        ?>

        <div class="row g-3 mb-4 dashboard-metrics-row">
            <div class="col-12 col-md-4">
                <a href="<?php echo e(route('customer.orders')); ?>" class="summary-card summary-link" aria-label="View total orders">
                    <i class="bi bi-receipt summary-watermark"></i>
                    <div class="summary-label">Total Orders</div>
                    <div class="summary-value"
                         data-counter-target="<?php echo e((int) ($totalOrders ?? 0)); ?>"
                         data-counter-type="number"><?php echo e(number_format($totalOrders ?? 0)); ?></div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="<?php echo e(route('customer.orders', ['status' => 'completed'])); ?>" class="summary-card summary-link"
                    aria-label="View completed orders">
                <i class="bi bi-cash-stack summary-watermark"></i>
                <div class="summary-label">Total Spent</div>
                <div class="summary-value"
                     data-counter-target="<?php echo e((float) ($totalSpent ?? 0)); ?>"
                     data-counter-type="money"
                     data-counter-currency="Tsh"
                     data-counter-precision="1"><?php echo e(format_money_short($totalSpent ?? 0)); ?></div>
            </a>
        </div>
            <div class="col-12 col-md-4">
                <a href="<?php echo e(route('cart.index')); ?>" class="summary-card summary-link" aria-label="View cart items">
                    <i class="bi bi-cart-fill summary-watermark"></i>
                    <div class="summary-label">Cart Items</div>
                    <div class="summary-value"
                         data-counter-target="<?php echo e((int) ($cartItemsCount ?? 0)); ?>"
                         data-counter-type="number"><?php echo e(number_format($cartItemsCount ?? 0)); ?></div>
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                        <a href="<?php echo e(route('customer.orders')); ?>" class="btn btn-sm dashboard-primary-btn">
                            <i class="bi bi-list-ul me-1"></i>View all
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if($recentOrders->isNotEmpty()): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Order</th>

                                            <th>Items</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="pe-3 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $statusClass = match($order->status) {
                                                    'pending' => 'status-badge-soft-warning',
                                                    'confirmed' => 'status-badge-soft-info',
                                                    'completed' => 'status-badge-soft-success',
                                                    'cancelled' => 'status-badge-soft-danger',
                                                    default => 'text-bg-light border',
                                                };
                                            ?>
                                            <tr>
                                                <td class="ps-3 font-monospace"><?php echo e($order->order_number ?: $order->public_id); ?></td>

                                                <td><?php echo e($order->orderItems->count()); ?></td>
                                                <td><?php echo e(format_money_short($order->total_amount, 0)); ?></td>
                                                <td>
                                                    <span
                                                        class="badge text-capitalize <?php echo e($statusClass); ?>"><?php echo e($order->status); ?></span>
                                                </td>
                                                <td class="pe-3 text-end">
                                                    <a href="<?php echo e(route('customer.order.details', $order)); ?>"
                                                        class="btn btn-sm dashboard-icon-btn" aria-label="View order">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="dashboard-empty-state p-4">
                                <i class="bi bi-receipt-cutoff"></i>
                                <p class="mb-0">No recent orders yet.</p>
                                <a href="<?php echo e(route('shop')); ?>" class="btn btn-sm dashboard-primary-btn">
                                    <i class="bi bi-shop me-1"></i>Start shopping
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Order Overview</h5>
                    </div>
                    <div class="card-body">
                        <?php if(($totalOrders ?? 0) > 0): ?>
                            <div class="order-donut-wrap">
                                <div class="order-donut-chart"
                                    data-completed-deg="<?php echo e($completedDeg); ?>"
                                    data-pending-deg="<?php echo e($pendingDeg); ?>"
                                    data-cancelled-deg="<?php echo e($cancelledDeg); ?>"
                                    style="--completed-deg: 0deg; --pending-deg: 0deg; --cancelled-deg: 0deg;">
                                    <div class="order-donut-center">
                                        <div class="order-donut-total"><?php echo e(number_format($totalOrders ?? 0)); ?></div>
                                        <small>Total Orders</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3 text-muted small">
                                <span class="status-dot status-dot-completed"></span>Completed: <?php echo e(number_format($completedOrders)); ?> |
                                <span class="status-dot status-dot-pending"></span>Pending: <?php echo e(number_format($pendingOrders)); ?> |
                                <span class="status-dot status-dot-cancelled"></span>Cancelled: <?php echo e(number_format($cancelledOrders)); ?>

                            </div>
                        <?php else: ?>
                            <div class="dashboard-empty-state">
                                <i class="bi bi-pie-chart"></i>
                                <p class="mb-0">No order data yet.</p>
                                <a href="<?php echo e(route('shop')); ?>" class="btn btn-sm dashboard-primary-btn">
                                    <i class="bi bi-shop me-1"></i>Start shopping
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 d-none d-lg-block">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Account Info</h5>
                        <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-sm dashboard-primary-btn">
                            <i class="bi bi-person me-1"></i>Edit Profile
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="account-panels">
                            <div class="account-panel">
                                <div class="account-info-line">
                                    <span class="account-info-key">Name:</span>
                                    <span class="account-info-value"><?php echo e(strtoupper((string) ($profileUser->name ?? 'N/A'))); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Email:</span>
                                    <span class="account-info-value"><?php echo e($profileUser->email ?? 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Phone:</span>
                                    <span class="account-info-value"><?php echo e($profileUser->phone_number ?? 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Role:</span>
                                    <span class="account-info-value"><?php echo e(isset($profileUser->role) ? ucfirst((string) $profileUser->role) : 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Joined:</span>
                                    <span class="account-info-value"><?php echo e(optional($profileUser->created_at)->format('d M Y') ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="account-panel account-stats-panel">
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Orders:</span>
                                    <span class="account-info-value"><?php echo e(number_format($totalOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Cancelled:</span>
                                    <span class="account-info-value"><?php echo e(number_format($cancelledOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Completed:</span>
                                    <span class="account-info-value"><?php echo e(number_format($completedOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Spent:</span>
                                    <span class="account-info-value"><?php echo e(format_money_short($totalSpent ?? 0, 0)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Spending (Last 6 Months)</h5>
                    </div>
                    <div class="card-body">
                        <?php if($monthlySpending->count() > 0): ?>
                            <div class="mb-3 spending-chart-wrap">
                                <canvas id="customerSpendingChart"
                                    data-monthly-spending='<?php echo json_encode($monthlySpendingChart, 15, 512) ?>'
                                    data-y-axis='<?php echo json_encode($yAxisConfig, 15, 512) ?>'
                                    aria-label="Last 6 months spending line graph"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="dashboard-empty-state">
                                <i class="bi bi-cash-stack"></i>
                                <p class="mb-0">No spending data available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-lg-none">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Account Info</h5>
                        <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-sm dashboard-primary-btn">
                            <i class="bi bi-person me-1"></i>Edit Profile
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="account-panels">
                            <div class="account-panel">
                                <div class="account-info-line">
                                    <span class="account-info-key">Name:</span>
                                    <span class="account-info-value"><?php echo e(strtoupper((string) ($profileUser->name ?? 'N/A'))); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Email:</span>
                                    <span class="account-info-value"><?php echo e($profileUser->email ?? 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Phone:</span>
                                    <span class="account-info-value"><?php echo e($profileUser->phone_number ?? 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Role:</span>
                                    <span class="account-info-value"><?php echo e(isset($profileUser->role) ? ucfirst((string) $profileUser->role) : 'N/A'); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Joined:</span>
                                    <span class="account-info-value"><?php echo e(optional($profileUser->created_at)->format('d M Y') ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="account-panel account-stats-panel">
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Orders:</span>
                                    <span class="account-info-value"><?php echo e(number_format($totalOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Cancelled:</span>
                                    <span class="account-info-value"><?php echo e(number_format($cancelledOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Completed:</span>
                                    <span class="account-info-value"><?php echo e(number_format($completedOrders ?? 0)); ?></span>
                                </div>
                                <div class="account-info-line">
                                    <span class="account-info-key">Total Spent:</span>
                                    <span class="account-info-value"><?php echo e(format_money_short($totalSpent ?? 0, 0)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card clean-card h-100">
                    <div class="card-header clean-card-header">
                        <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Confirmed Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="list-row">
                                <div>
                                    <div><?php echo e($activity['message']); ?></div>
                                    <small class="text-muted"><?php echo e($activity['date']->diffForHumans()); ?></small>
                                </div>
                                <?php if(isset($activity['amount'])): ?>
                                    <span class="text-muted"><?php echo e(format_money_short($activity['amount'], 0)); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="dashboard-empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p class="mb-0">No recent confirmed orders.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo e(asset('js/customer-dashboard.js')); ?>?v=<?php echo e(filemtime(public_path('js/customer-dashboard.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/customer/dashboard.blade.php ENDPATH**/ ?>