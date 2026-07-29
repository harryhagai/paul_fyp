


<?php $__env->startSection('title', 'Shop - KidsStore'); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/shop.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="shop-container">
<?php
    $hasActiveFilter = filled(request('search')) || filled(request('category'));
?>
<!-- Search and Categories in Header -->
<div class="shop-header-sticky">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Search Bar Section -->
                <div class="search-section mb-3">
                    <?php
                        $selectedSort = request('sort_by') && request('sort_order') ? request('sort_by') . '-' . request('sort_order') : '';
                    ?>
                    <form method="GET" action="<?php echo e(route('shop')); ?>" id="shop-search-form">
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap flex-md-nowrap search-sort-row">
                            <div class="search-bar position-relative search-bar-compact">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo e(request('search')); ?>">
                                <button type="submit" class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <select class="form-select form-select-sm shop-sort-select" id="shop-sort-select" aria-label="Sort products">
                                <option value="">Sort by</option>
                                <option value="created_at-desc" <?php echo e($selectedSort === 'created_at-desc' ? 'selected' : ''); ?>>Newest</option>
                                <option value="name-asc" <?php echo e($selectedSort === 'name-asc' ? 'selected' : ''); ?>>Name (A-Z)</option>
                                <option value="name-desc" <?php echo e($selectedSort === 'name-desc' ? 'selected' : ''); ?>>Name (Z-A)</option>
                                <option value="new_price-asc" <?php echo e($selectedSort === 'new_price-asc' ? 'selected' : ''); ?>>Price (Low to High)</option>
                                <option value="new_price-desc" <?php echo e($selectedSort === 'new_price-desc' ? 'selected' : ''); ?>>Price (High to Low)</option>
                                <option value="rate-desc" <?php echo e($selectedSort === 'rate-desc' ? 'selected' : ''); ?>>Highest Rated</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Categories Section -->
                <div class="categories-header-section">
                    <div class="categories-grid d-flex gap-2 justify-content-start" id="shop-categories-grid">
                        <?php
                            $allCategoriesQuery = request()->query();
                            unset($allCategoriesQuery['category']);
                        ?>
                        <a href="<?php echo e(route('shop')); ?>?<?php echo e(http_build_query($allCategoriesQuery)); ?>" class="category-pill btn btn-sm <?php echo e(!request('category') ? 'category-pill-selected' : 'category-pill-default'); ?>">
                            <i class="bi bi-grid-fill me-1"></i>All Categories
                        </a>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $categoryQuery = request()->query();
                                unset($categoryQuery['search']);
                                $categoryQuery['category'] = $category->id;
                            ?>
                        <a href="<?php echo e(route('shop')); ?>?<?php echo e(http_build_query($categoryQuery)); ?>" class="category-pill btn btn-sm <?php echo e(request('category') == $category->id ? 'category-pill-selected' : 'category-pill-default'); ?>">
                                <?php echo e($category->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controls Bar -->
<div class="controls-bar" style="display: none;">
</div>

    <!-- Products Grid -->
    <?php if(!$hasActiveFilter && !empty($featuredRows)): ?>
        <div class="mb-4 featured-rows-scroll">
            <?php $__currentLoopData = ['row1', 'row2', 'row3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $rowProducts = $featuredRows[$rowKey] ?? collect();
                    $rowTitle = $featuredRows[$rowKey . '_title'] ?? '';
                    $rowIcon = match($rowKey) {
                        'row1' => 'bi-box-seam',
                        'row2' => 'bi-star',
                        'row3' => 'bi-eye',
                        default => 'bi-grid-fill'
                    };
                ?>
                <?php if($rowProducts->isNotEmpty()): ?>
                    <section class="mb-4 featured-row-section featured-row-panel">
                        <h2 class="h5 fw-bold mb-3 featured-row-title">
                            <i class="bi <?php echo e($rowIcon); ?> me-2"></i><?php echo e($rowTitle); ?>

                        </h2>
                        <div class="products-grid featured-products-grid">
                            <?php $__currentLoopData = $rowProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="product-card featured-product-card">
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
                                    </div>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mobile-scroll-indicator" aria-hidden="true">
                            <span class="mobile-scroll-thumb"></span>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="products-grid" id="productsContainer">
        <?php if($products->count() > 0): ?>
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
        <div class="no-products-found">
            <div class="shop-empty text-center p-4 p-md-5">
                <i class="bi bi-search d-inline-block mb-2"></i>
                <h3 class="h5 mb-2">No products found</h3>
                <p class="mb-3">Try adjusting your search criteria or browse different categories</p>
                <a href="<?php echo e(route('shop')); ?>" class="btn shop-empty-btn">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <!-- Pagination -->
    <div id="shop-pagination" style="display:none;">
        <?php echo e($products->appends(request()->query())->links()); ?>

    </div>
    <div id="shop-infinite-loader" class="text-center py-3" style="display:none;">
        <span class="loading-spinner"></span>
    </div>
    <div id="shop-infinite-end" class="text-center py-2 text-muted small" style="display:none;">
        No more products
    </div>
    <div id="shop-scroll-sentinel" style="height: 1px;"></div>

</main>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/shop.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\shop.blade.php ENDPATH**/ ?>