
<?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr>
        <td class="fw-semibold font-monospace"><?php echo e($order->order_number ?: $order->public_id); ?></td>
        <td class="orders-col-date"><?php echo e(optional($order->ordered_at)->format('M d, Y H:i')); ?></td>
        <td>
            <?php
                $itemsSummary = $order->orderItems->map(function ($item) {
                    $name = $item->product->name ?? 'Unavailable product';
                    return \Illuminate\Support\Str::limit($name, 24) . ' (' . $item->quantity . ')';
                });
                $visibleItems = $itemsSummary->take(2);
                $hiddenItemsCount = max(0, $itemsSummary->count() - $visibleItems->count());
                $totalItems = (int) $order->orderItems->sum('quantity');
            ?>
            <div class="d-inline d-md-none fw-semibold"><?php echo e($totalItems); ?></div>
            <div class="order-items-compact d-none d-md-flex" title="<?php echo e($itemsSummary->implode(', ')); ?>">
                <?php $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemText): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="order-item-chip"><?php echo e($itemText); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($hiddenItemsCount > 0): ?>
                    <span class="order-item-more">+<?php echo e($hiddenItemsCount); ?> more</span>
                <?php endif; ?>
            </div>
        </td>
        <td class="fw-semibold"><?php echo e(format_money_short($order->total_amount, 2)); ?></td>
        <td>
            <span class="badge text-capitalize <?php echo e($order->status === 'cancelled' ? 'status-badge-soft-danger' : 'text-bg-light border'); ?>"><?php echo e($order->status_text); ?></span>
        </td>
        <td class="text-end orders-actions-cell">
            <div class="btn-group" role="group">
                <a href="<?php echo e(route('customer.order.details', $order)); ?>" class="btn btn-sm btn-outline-primary themed-outline-btn view-order-btn" aria-label="View order <?php echo e($order->order_number); ?>">
                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="btn-text"><i class="bi bi-eye me-1"></i>View</span>
                </a>
                <?php if($order->canBeCancelled()): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="<?php echo e($order->public_id); ?>" data-order-number="<?php echo e($order->order_number); ?>">
                        <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                        <span class="btn-text"><i class="bi bi-x-circle me-1"></i>Cancel</span>
                    </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr class="orders-empty-row">
        <td colspan="6">
            <div class="empty-state">
                <i class="bi bi-receipt-cutoff"></i>
                <p class="mb-0">No orders found.</p>
                <small class="text-muted">Try changing your search, status, or date filters.</small>
            </div>
        </td>
    </tr>
<?php endif; ?>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\customer\partials\order_rows.blade.php ENDPATH**/ ?>