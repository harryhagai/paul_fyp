


<?php $__env->startSection('title', 'Categories - KidsStore365'); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(asset('css/categories.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="categories-page py-4 py-md-5">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h1 class="categories-title mb-2">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Shop by Category
                </h1>
                <p class="categories-subtitle mb-0">
                    Discover collections for your little ones.
                </p>
            </div>

            <div class="categories-search-wrap mb-4 mb-md-5">
                <form method="GET" action="<?php echo e(route('categories')); ?>" class="row g-2 align-items-center"
                    id="categories-search-form">
                    <div class="col">
                        <input type="text" name="search" class="form-control categories-search-input"
                            placeholder="Search categories..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn categories-search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div id="categories-results">
                <div class="row g-3 g-md-4">
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $icons = [
                                'baby-clothes' => 'bi-t-shirt',
                                'kids-toys' => 'bi-rocket-takeoff',
                                'gifts-hampers' => 'bi-gift',
                            ];
                            $icon = $icons[$category->slug] ?? 'bi-circle';
                        ?>

                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="<?php echo e(route('category.show', $category->slug)); ?>"
                                class="category-tile card h-100 text-decoration-none">
                                <div class="card-body d-flex flex-column">
                                    <div class="category-icon mb-2">
                                        <i class="bi <?php echo e($icon); ?>"></i>
                                    </div>
                                    <h2 class="category-name mb-2"><?php echo e($category->name); ?></h2>
                                    <?php if($category->description): ?>
                                        <p class="category-description mb-3"><?php echo e(Str::limit($category->description, 70)); ?></p>
                                    <?php endif; ?>
                                    <div class="category-meta mt-auto">
                                        <i class="bi bi-box-seam me-1"></i>
                                        <?php echo e($category->products_count); ?> <?php echo e(Str::plural('product', $category->products_count)); ?>

                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12">
                            <div class="categories-empty text-center p-4 p-md-5">
                                <i class="bi bi-search mb-3 d-inline-block"></i>
                                <h3 class="h5 mb-2">No categories found</h3>
                                <p class="mb-3">Try a different search keyword.</p>
                                <div class="row g-2 justify-content-start">
                                    <div class="col-6 col-sm-auto">
                                        <a href="<?php echo e(route('shop')); ?>" class="btn btn-primary w-100">Browse Products</a>
                                    </div>
                                    <div class="col-12 col-sm-auto">
                                        <a href="<?php echo e(route('categories')); ?>" class="btn btn-outline-primary w-100">Clear Search</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/categories.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\categories.blade.php ENDPATH**/ ?>