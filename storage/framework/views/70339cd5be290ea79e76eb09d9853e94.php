


<?php $__env->startSection('title', 'Orders Management - KidsStore Seller'); ?>

<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/sellerorder.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid mt-2 seller-orders-page">
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 orders-page-title">
                        <i class="bi bi-receipt me-3"></i>Orders Management
                    </h1>
                    <p class="orders-page-subtitle mb-0">Track orders, update status, and review customer purchase details.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary themed-outline-btn" id="refreshBtn">
                        <i class="bi bi-arrow-repeat me-1"></i>Refresh
                    </button>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefresh">
                        <label class="form-check-label" for="autoRefresh">Auto Refresh</label>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4 search-filter">
                <div class="card-body">
                    <form id="searchForm" method="GET" action="<?php echo e(route('seller.orders')); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search order number, customer, status..." value="<?php echo e(request('search')); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
                                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>" placeholder="From Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>" placeholder="To Date">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary themed-outline-btn w-100">
                                    <i class="bi bi-filter me-2"></i>Filter
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="<?php echo e(route('seller.orders')); ?>" class="btn btn-outline-secondary themed-outline-btn w-100">
                                    <i class="bi bi-arrow-repeat me-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Orders List</h5>
                        <small class="text-muted">Status flow: Pending -> Confirmed -> Completed (or Cancelled before completion)</small>
                    </div>
                    <div class="text-muted small">
                        Total: <?php echo e($orders->total()); ?> orders
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Order Number</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <?php echo $__env->make('seller.partials.order_rows', ['orders' => $orders], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="lazyLoader" class="text-center py-3 d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading more orders...
                    </div>
                    <div id="scrollSentinel" class="lazy-sentinel" aria-hidden="true"></div>

                    <div class="pagination-wrapper d-none" id="ordersPagination" style="margin-top: 2px;">
                        <div class="d-flex justify-content-center">
                            <?php echo e($orders->links('pagination::bootstrap-5')); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade zoom-modal order-confirmation-modal" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-5" id="viewOrderModalLabel">
                    <span class="confirmation-title"><i class="bi bi-receipt me-2"></i>Order Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-edit themed-outline-btn" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Seller Orders JavaScript -->
<script src="<?php echo e(asset('js/sellerorder.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/seller/orders.blade.php ENDPATH**/ ?>