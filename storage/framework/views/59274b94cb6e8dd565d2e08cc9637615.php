
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="category-summary-item" data-category-id="<?php echo e($category->public_id); ?>">
        <div class="category-summary-card h-100">
            <i
                class="bi bi-<?php echo e($category->products_count > 0 ? 'box-seam' : 'folder'); ?> category-summary-watermark"></i>
            <div class="category-summary-name"><?php echo e($category->name); ?></div>
            <div class="category-summary-value"><?php echo e(number_format($category->products_count)); ?> Products</div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\seller\partials\category_summary_items.blade.php ENDPATH**/ ?>