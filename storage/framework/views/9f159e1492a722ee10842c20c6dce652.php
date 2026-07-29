
<?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
    $currentStatus = strtolower((string) $order->status);
    $statusFlow = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];
    $allowedNextStatuses = $statusFlow[$currentStatus] ?? [];
?>
<tr>
    <td>
        <?php $orderNumber = $order->order_number ?: $order->public_id; ?>
        <strong class="font-monospace" title="<?php echo e($orderNumber); ?>"><?php echo e($orderNumber); ?></strong>
    </td>
    <td>
        <div class="customer-info">
            <div class="customer-avatar">
                <?php echo e(strtoupper(substr($order->user->name, 0, 1))); ?>

            </div>
            <div>
                <div class="fw-bold"><?php echo e($order->user->name); ?></div>
                <small class="text-muted"><?php echo e($order->user->email); ?></small>
                <div><small class="text-muted"><?php echo e($order->user->phone_number ?: 'N/A'); ?></small></div>
            </div>
        </div>
    </td>
    <td>
        <?php echo e($order->orderItems->count()); ?> item<?php echo e($order->orderItems->count() > 1 ? 's' : ''); ?>

    </td>
    <td>
        <strong><?php echo e(format_money_short($order->total_amount, 0)); ?></strong>
    </td>
    <td>
        <span class="order-status-badge status-<?php echo e($order->status); ?>">
            <?php echo e(ucfirst($order->status)); ?>

        </span>
    </td>
    <td>
        <div class="small text-muted">
            <?php echo e($order->created_at->format('d M Y')); ?>

        </div>
        <div class="small">
            <?php echo e($order->created_at->format('H:i')); ?>

        </div>
    </td>
    <td>
        <div class="d-flex gap-1 align-items-start flex-wrap">
            <button class="btn btn-sm btn-outline-primary themed-outline-btn action-btn view-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewOrderModal"
                    data-order-id="<?php echo e($order->public_id); ?>">
                <i class="bi bi-eye me-1"></i>View
            </button>
            <?php $__currentLoopData = $allowedNextStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nextStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $btnClass = match($nextStatus) {
                        'confirmed' => 'btn-outline-success',
                        'completed' => 'btn-outline-success',
                        'cancelled' => 'btn-outline-danger',
                        default => 'btn-outline-secondary',
                    };
                    $btnIcon = match($nextStatus) {
                        'confirmed' => 'bi-patch-check',
                        'completed' => 'bi-check2-circle',
                        'cancelled' => 'bi-x-circle',
                        default => 'bi-arrow-right-circle',
                    };
                    $btnLabel = match($nextStatus) {
                        'confirmed' => 'Confirm',
                        'completed' => 'Complete',
                        'cancelled' => 'Cancel',
                        default => ucfirst($nextStatus),
                    };
                ?>
                <button type="button"
                        class="btn btn-sm action-btn status-action-btn <?php echo e($btnClass); ?>"
                        data-order-id="<?php echo e($order->public_id); ?>"
                        data-current-status="<?php echo e($currentStatus); ?>"
                        data-new-status="<?php echo e($nextStatus); ?>"
                        data-order-number="<?php echo e($orderNumber); ?>">
                    <i class="bi <?php echo e($btnIcon); ?> me-1"></i><?php echo e($btnLabel); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($allowedNextStatuses)): ?>
                <span class="badge text-bg-light border">Status locked</span>
            <?php endif; ?>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr>
    <td colspan="7" class="text-center py-4">
        <div class="orders-empty-state mx-auto">
            <div class="orders-empty-icon-wrap">
                <i class="bi bi-receipt-cutoff orders-empty-icon"></i>
            </div>
            <h6 class="orders-empty-title mb-1">No orders found</h6>
            <p class="orders-empty-text mb-0">Try changing search filters or date range.</p>
        </div>
    </td>
</tr>
<?php endif; ?>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/seller/partials/order_rows.blade.php ENDPATH**/ ?>