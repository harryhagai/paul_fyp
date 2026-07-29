
<?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr class="product-card">
    <td><?php echo e($loop->iteration + (($products->currentPage() - 1) * $products->perPage())); ?></td>
    <td>
        <?php if($product->thumbnail): ?>
            <img src="<?php echo e(asset('storage/' . $product->thumbnail)); ?>" alt="<?php echo e($product->name); ?>" class="thumbnail-preview thumbnail-clickable" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="<?php echo e($product->public_id); ?>">
        <?php else: ?>
            <div class="thumbnail-placeholder thumbnail-clickable" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="<?php echo e($product->public_id); ?>">
                <i class="bi bi-image"></i>
            </div>
        <?php endif; ?>
    </td>
    <td>
        <div class="d-flex flex-column">
            <strong><?php echo e($product->name); ?></strong>
            <small class="text-muted"><?php echo e($product->slug); ?></small>
        </div>
    </td>
    <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
    <td>
        <div class="d-flex flex-column">
            <strong><?php echo e(format_money_short($product->new_price, 2)); ?></strong>
            <?php if($product->old_price): ?>
                <small class="text-decoration-line-through text-muted">
                    <?php echo e(format_money_short($product->old_price, 2)); ?>

                </small>
            <?php endif; ?>
        </div>
    </td>
    <td>
        <?php echo e($product->stock); ?>

    </td>
    <td>
        <?php if($product->discount > 0): ?>
            <span class="discount-badge"><?php echo e($product->discount); ?>% OFF</span>
        <?php else: ?>
            <span class="badge bg-secondary">No Discount</span>
        <?php endif; ?>
    </td>
    <td>
        <select class="form-select rating-select" data-product-id="<?php echo e($product->public_id); ?>" style="width: 70px;">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo e($i); ?>" <?php echo e($i == $product->rate ? 'selected' : ''); ?>><?php echo e($i); ?></option>
            <?php endfor; ?>
        </select>
    </td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input advertised-toggle" type="checkbox" data-product-id="<?php echo e($product->public_id); ?>" <?php echo e($product->is_advertised ? 'checked' : ''); ?>>
            <label class="form-check-label"><?php echo e($product->is_advertised ? 'Advertised' : 'Normal'); ?></label>
        </div>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="<?php echo e(route('seller.products.media', $product->public_id)); ?>" class="btn btn-sm btn-outline-success action-btn themed-outline-btn" title="Manage Media"><i class="bi bi-images"></i></a>
            <button class="btn btn-sm btn-outline-primary action-btn themed-outline-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editProductModal" data-product-id="<?php echo e($product->public_id); ?>"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger action-btn delete-btn" data-product-id="<?php echo e($product->public_id); ?>"><i class="bi bi-trash"></i></button>
            <button class="btn btn-sm btn-outline-info action-btn view-btn" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="<?php echo e($product->public_id); ?>"><i class="bi bi-eye"></i></button>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr>
    <td colspan="10" class="text-center py-4">
        <div class="products-empty-state mx-auto text-center">
            <div class="products-empty-icon-wrap">
                <i class="bi bi-box-seam products-empty-icon"></i>
            </div>
            <h6 class="products-empty-title mb-1">No products found</h6>
            <p class="products-empty-text mb-0">Try changing filters or add a new product.</p>
        </div>
    </td>
</tr>
<?php endif; ?>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/seller/partials/product_rows.blade.php ENDPATH**/ ?>