
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr data-category-id="<?php echo e($category->public_id); ?>">
        <td class="category-name-col"><?php echo e($category->name); ?></td>
        <td class="category-description-col"><?php echo e($category->description ?: 'No description'); ?></td>
        <td class="text-center fit-content-col">
            <span class="badge category-count-badge"><?php echo e($category->products_count); ?></span>
        </td>
        <td class="fit-content-col"><?php echo e($category->created_at->format('d M Y')); ?></td>
        <td class="text-center fit-content-col text-nowrap">
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="showCategory(<?php echo \Illuminate\Support\Js::from($category->public_id)->toHtml() ?>)">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="editCategory(<?php echo \Illuminate\Support\Js::from($category->public_id)->toHtml() ?>)">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo \Illuminate\Support\Js::from($category->public_id)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($category->name)->toHtml() ?>)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/seller/partials/category_rows.blade.php ENDPATH**/ ?>