


<?php $__env->startSection('title', $category->name . ' - KidsStore365'); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(asset('css/shop.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/category.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="category-page py-4 py-md-5">
    <div class="container">
        <div class="category-hero card border-0 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <h1 class="category-title mb-1"><?php echo e($category->name); ?></h1>
                        <?php if($category->description): ?>
                            <p class="category-subtitle mb-0"><?php echo e($category->description); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="category-count badge rounded-pill"><?php echo e($products->total()); ?> products</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="category-controls card border-0 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="<?php echo e(route('category.show', $category->slug)); ?>" id="category-search-form" class="row g-2">
                    <div class="col-12 col-md">
                        <input type="text" name="search" class="form-control category-search-input" placeholder="Search in <?php echo e($category->name); ?>..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <?php
                            $selectedSort = request('sort_by') && request('sort_order') ? request('sort_by') . '-' . request('sort_order') : '';
                        ?>
                        <select name="sort" class="form-select" id="sort-select">
                            <option value="">Sort by</option>
                            <option value="name-asc" <?php echo e($selectedSort === 'name-asc' ? 'selected' : ''); ?>>Name (A-Z)</option>
                            <option value="name-desc" <?php echo e($selectedSort === 'name-desc' ? 'selected' : ''); ?>>Name (Z-A)</option>
                            <option value="new_price-asc" <?php echo e($selectedSort === 'new_price-asc' ? 'selected' : ''); ?>>Price (Low to High)</option>
                            <option value="new_price-desc" <?php echo e($selectedSort === 'new_price-desc' ? 'selected' : ''); ?>>Price (High to Low)</option>
                            <option value="created_at-desc" <?php echo e($selectedSort === 'created_at-desc' ? 'selected' : ''); ?>>Newest First</option>
                            <option value="rate-desc" <?php echo e($selectedSort === 'rate-desc' ? 'selected' : ''); ?>>Highest Rated</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-auto d-grid">
                        <button type="submit" class="btn category-search-btn"><i class="bi bi-search"></i></button>
                    </div>
                    <input type="hidden" name="sort_by" value="<?php echo e(request('sort_by')); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo e(request('sort_order')); ?>">
                    <input type="hidden" name="in_stock" value="<?php echo e(request('in_stock')); ?>">
                    <input type="hidden" name="on_sale" value="<?php echo e(request('on_sale')); ?>">
                    <input type="hidden" name="rating" value="<?php echo e(request('rating')); ?>">
                </form>

                <div class="d-flex flex-wrap gap-2 mt-3" id="filter-actions">
                    <?php
                        $queryParams = request()->query();
                        $isInStock = request('in_stock') == '1';
                        $isOnSale = request('on_sale') == '1';
                        $isHighRating = request('rating') == '4';
                    ?>

                    <a href="<?php echo e(route('category.show', $category->slug, array_merge($queryParams, ['in_stock' => $isInStock ? null : '1', 'page' => null]))); ?>" class="btn btn-sm filter-chip <?php echo e($isInStock ? 'active' : ''); ?>" data-filter-link>
                        <i class="bi bi-check-circle me-1"></i>In Stock
                    </a>
                    <a href="<?php echo e(route('category.show', $category->slug, array_merge($queryParams, ['on_sale' => $isOnSale ? null : '1', 'page' => null]))); ?>" class="btn btn-sm filter-chip <?php echo e($isOnSale ? 'active' : ''); ?>" data-filter-link>
                        <i class="bi bi-percent me-1"></i>On Sale
                    </a>
                    <a href="<?php echo e(route('category.show', $category->slug, array_merge($queryParams, ['rating' => $isHighRating ? null : '4', 'page' => null]))); ?>" class="btn btn-sm filter-chip <?php echo e($isHighRating ? 'active' : ''); ?>" data-filter-link>
                        <i class="bi bi-star-fill me-1"></i>4+ Stars
                    </a>
                    <?php if($isInStock || $isOnSale || $isHighRating || request('search') || request('sort_by') || request('sort_order')): ?>
                        <a href="<?php echo e(route('category.show', $category->slug)); ?>" class="btn btn-sm filter-chip clear" data-filter-link>
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="other-categories mb-4">
            <div class="d-flex flex-wrap gap-2">
                <?php $__currentLoopData = $categories->where('id', '!=', $category->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('category.show', $cat->slug)); ?>" class="btn btn-sm btn-light border">
                        <?php echo e($cat->name); ?>

                        <span class="badge text-bg-secondary ms-1"><?php echo e($cat->products_count ?? $cat->products->count()); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div id="category-results">
            <div class="products-grid" id="productsContainer">
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="product-card">
                        <div class="product-image">
                            <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                                <img src="<?php echo e($product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png')); ?>" alt="<?php echo e($product->name); ?>" loading="lazy">
                            </a>
                            <div class="product-badges">
                                <?php if($product->created_at->diffInDays(now()) <= 7): ?>
                                    <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                                    <?php echo e($product->name); ?>

                                </a>
                            </h3>

                            <?php if($product->description?->description): ?>
                            <p class="product-description">
                                <?php echo e(Str::limit($product->description->description, 60)); ?>

                            </p>
                            <?php endif; ?>

                            <div class="product-prices">
                                <span class="product-price">Tsh <?php echo e(number_format((float) $product->new_price, 0)); ?></span>
                                <?php if($product->old_price && $product->old_price > $product->new_price): ?>
                                    <span class="product-old-price">Tsh <?php echo e(number_format((float) $product->old_price, 0)); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="product-rating">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($product->rate > 0): ?>
                                            <i class="bi <?php echo e($i <= round($product->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star star text-secondary"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo e(number_format((float) $product->rate, 1)); ?>)</span>
                                <span class="stock-status <?php echo e($product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                    <?php if($product->stock > 10): ?>
                                        In Stock: <?php echo e($product->stock); ?>

                                    <?php elseif($product->stock > 0): ?>
                                        In Stock: <?php echo e($product->stock); ?>

                                    <?php else: ?>
                                        Out of Stock
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="product-meta">
                                <span class="category">
                                    <i class="bi bi-tag-fill"></i> <?php echo e($product->category->name ?? 'Uncategorized'); ?>

                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="category-empty-wrap">
                    <div class="category-empty text-center p-4 p-md-5">
                        <i class="bi bi-search d-inline-block mb-2"></i>
                        <h3 class="h5">No products found</h3>
                        <p class="mb-3">Try adjusting search or filters.</p>
                        <a href="<?php echo e(route('category.show', $category->slug)); ?>" class="btn category-reset-btn">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 category-pagination-wrap">
                <?php echo e($products->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/category.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\category.blade.php ENDPATH**/ ?>