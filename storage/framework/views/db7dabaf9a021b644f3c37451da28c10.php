


<?php $__env->startSection('title', $product->name . ' - KidsStore'); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/shop.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/show.css')); ?>">
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;">
        <div class="notification-content">
            <i class="bi bi-check-circle"></i>
            <span id="notification-message"></span>
        </div>
    </div>

    <div class="container py-3 py-md-4">
    <!-- Shop Menu Icon -->
    <div class="shop-menu-icon mb-3">
        <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline-secondary btn-sm show-dot-btn" data-spin-link="1" title="Back to Shop">
            <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="button-text"><i class="bi bi-arrow-left"></i> Back to Shop</span>
        </a>
    </div>

    <div class="product-details">
        <div class="row g-4">
            <div class="col-lg-6">
                <!-- Product Gallery -->
                <div class="product-gallery card border-0 shadow-sm p-2 p-md-3">
                    <!-- Top row: Main image and left horizontal thumbnails -->
                    <div class="gallery-top-row">
                        <div class="gallery-sidebar">
                            <div class="gallery-thumbs-vertical">
                                <?php
                                    $images = $product->media->where('type', 'image') ?: collect();
                                    $primaryImage = $images->where('is_primary', true)->first() ?: $images->first();
                                ?>

                                <?php if($primaryImage): ?>
                                    <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="thumb-image <?php echo e($loop->first ? 'active' : ''); ?>"
                                            data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                            onclick="changeImage('<?php echo e(asset('storage/' . $image->file_path)); ?>', this)">
                                            <img src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                                alt="<?php echo e($product->name); ?>">
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="main-image" id="mainImageFrame">
                            <?php
                                $mainImage = $primaryImage
                                    ? asset('storage/' . $primaryImage->file_path)
                                    : asset('img/logo.png');
                            ?>
                            <?php if($primaryImage && $images->count() > 1): ?>
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-prev"
                                    onclick="galleryPrev()"
                                    aria-label="Previous image">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-next"
                                    onclick="galleryNext()"
                                    aria-label="Next image">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                            <img id="mainImage" src="<?php echo e($mainImage); ?>" alt="<?php echo e($product->name); ?>">
                        </div>
                    </div>

                    <?php if($primaryImage && $images->count() > 1): ?>
                        <div class="mobile-gallery-dots">
                            <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    class="gallery-dot <?php echo e($loop->first ? 'active' : ''); ?>"
                                    data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                    onclick="selectImageBySrc('<?php echo e(asset('storage/' . $image->file_path)); ?>')"
                                    aria-label="View image <?php echo e($loop->iteration); ?>">
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Bottom row: Horizontal thumbnails -->
                    <?php if($primaryImage && $images->count() > 1): ?>
                        <div class="horizontal-thumbs-container"> <!-- Add this wrapper -->
                            <div class="gallery-thumbs-horizontal">
                                <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="thumb-image-horizontal <?php echo e($loop->first ? 'active' : ''); ?>"
                                        data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                        onclick="changeImage('<?php echo e(asset('storage/' . $image->file_path)); ?>', this)">
                                        <img src="<?php echo e(asset('storage/' . $image->file_path)); ?>" alt="<?php echo e($product->name); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Product Info -->
                <div class="product-info card border-0 shadow-sm p-3 p-md-4">
                    <h1 class="product-main-title mb-2 fw-bold lh-sm"><?php echo e($product->name); ?>

                        <?php if($product->created_at > \Carbon\Carbon::now()->subDays(30)): ?>
                            <span class="badge bg-success align-middle">NEW</span>
                        <?php endif; ?>
                    </h1>

                    <?php
                        $allFiveStars = $product->ratings->count() > 0
                            && $product->ratings->every(fn ($review) => (int) $review->rating === 5);
                        $averageRating = $allFiveStars
                            ? 5.0
                            : round((float) ($product->ratings->avg('rating') ?? 0), 1);
                        $totalReviews = $product->ratings->count();
                    ?>
                    <?php if($averageRating > 0): ?>
                        <div class="product-rating">
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php
                                        $starValue = $averageRating - ($i - 1);
                                    ?>
                                    <i class="bi <?php echo e($starValue >= 1 ? 'bi-star-fill' : ($starValue >= 0.5 ? 'bi-star-half' : 'bi-star')); ?> star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo e(number_format($averageRating, 1)); ?> (<?php echo e($totalReviews); ?> reviews)</span>
                        </div>
                    <?php endif; ?>

                    <div class="product-prices align-items-center">
                        <span class="current-price fw-bold">Tsh <?php echo e(number_format((float) $product->new_price, 0)); ?></span>
                        <?php if($product->old_price && $product->old_price > $product->new_price): ?>
                            <span class="old-price text-danger">Tsh <?php echo e(number_format((float) $product->old_price, 0)); ?></span>
                            <span class="badge bg-danger badge-sm"><?php echo e($product->discount); ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <div class="stock-qty-row">
                        <div
                            class="stock-info <?php echo e($product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                            <i class="bi bi-circle-fill"></i>
                            <?php if($product->stock > 10): ?>
                                <span>In Stock (<?php echo e($product->stock); ?> available)</span>
                            <?php elseif($product->stock > 0): ?>
                                <span>Only <?php echo e($product->stock); ?> left in stock</span>
                            <?php else: ?>
                                <span>Out of Stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="quantity-selector">
                            <div class="quantity-input">
                                <button class="quantity-btn" onclick="changeQuantity(-1)" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>-</button>
                                <input type="number" id="quantityInput" value="<?php echo e($product->stock > 0 ? 1 : 0); ?>" min="1"
                                    max="<?php echo e($product->stock); ?>" readonly>
                                <button class="quantity-btn" onclick="changeQuantity(1)" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>+</button>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons row g-2">
                        <div class="col-12">
                        <button class="btn-add-cart w-100 show-dot-btn"
                            <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>

                            onclick="addToCart(<?php echo e($product->id); ?>, document.getElementById('quantityInput').value)">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-cart-plus"></i> <?php echo e($product->stock <= 0 ? 'Out of Stock' : 'Add to Cart'); ?></span>
                        </button>
                        </div>
                        <?php if(auth()->guard()->check()): ?>
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        <?php endif; ?>
                    </div>


                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-buttons-scrollable">
                <button class="tab-btn active" onclick="switchTab('description')">
                    <i class="bi bi-info-circle"></i>
                    <span>Description</span>
                </button>
                <button class="tab-btn" onclick="switchTab('specifications')">
                    <i class="bi bi-gear"></i>
                    <span>Specifications</span>
                </button>
                <button class="tab-btn" onclick="switchTab('reviews')">
                    <i class="bi bi-star"></i>
                    <span>Reviews</span>
                </button>
            </div>

            <div id="description" class="tab-content active">
                <h4>About this product</h4>
                <div class="prose">
                    <?php echo $product->description->description ?? '<p>No description available.</p>'; ?>

                    <?php echo $product->description->details ?? ''; ?>

                </div>
            </div>

            <div id="specifications" class="tab-content">
                <h4>Technical Specifications</h4>
                <div class="prose">
                    <?php if($product->description && $product->description->specifications): ?>
                        <?php
                            $specs = trim($product->description->specifications);
                            $lines = array_filter(explode("\n", $specs), function ($line) {
                                return trim($line) !== '';
                            });
                            if (count($lines) > 0) {
                                echo '<ul class="spec-list">';
                                foreach ($lines as $line) {
                                    if (strpos($line, ':') !== false) {
                                        [$key, $value] = explode(':', $line, 2);
                                        echo '<li><strong>' .
                                            htmlspecialchars(trim($key)) .
                                            ':</strong> ' .
                                            htmlspecialchars(trim($value)) .
                                            '</li>';
                                    } else {
                                        echo '<li>' . htmlspecialchars(trim($line)) . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>No specifications available.</p>';
                            }
                        ?>
                    <?php else: ?>
                        <p>No specifications available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="reviews" class="tab-content">
                <div class="reviews-heading">
                    <h4>Customer Reviews</h4>
                    <?php if($totalReviews > 0): ?>
                        <div class="reviews-heading-meta">
                            <span class="reviews-score"><?php echo e(number_format($averageRating, 1)); ?></span>
                            <div class="reviews-stars-inline">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo e($i <= round($averageRating) ? 'bi-star-fill' : 'bi-star'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="reviews-count"><?php echo e($totalReviews); ?> reviews</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($product->ratings->count() > 0): ?>
                    <div class="reviews-carousel-container">
                        <div class="css-carousel" id="cssReviewsCarousel">
                            <div class="css-carousel-track" style="--total-slides: <?php echo e($product->ratings->count()); ?>">
                                <?php $__currentLoopData = $product->ratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="css-carousel-slide">
                                        <div class="review-item">
                                            <div class="review-item-head">
                                                <div class="review-user">
                                                    <div class="review-avatar">
                                                        <?php echo e(strtoupper(substr($rating->user->name ?? 'U', 0, 1))); ?>

                                                    </div>
                                                    <div class="review-user-meta">
                                                        <strong class="review-author"><?php echo e($rating->user->name); ?></strong>
                                                        <small class="review-date">
                                                            <i class="bi bi-calendar3"></i>
                                                            <span><?php echo e($rating->created_at->format('M d, Y')); ?></span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="review-rating-summary">
                                                    <div class="stars review-stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i
                                                                class="bi <?php echo e($i <= $rating->rating ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="review-rating-badge"><?php echo e(number_format((float) $rating->rating, 1)); ?>/5</span>
                                                </div>
                                            </div>
                                            <?php if($rating->review): ?>
                                                <blockquote class="review-quote">
                                                    <p><?php echo e($rating->review); ?></p>
                                                </blockquote>
                                            <?php else: ?>
                                                <p class="review-empty">No review text provided</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>


                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No reviews yet. Be the first to rate this product!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if($relatedProducts->count() > 0): ?>
            <section class="related-products">
                <h2 class="related-title">You might also like</h2>
                <div class="products-grid">
                    <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="product-card">
                            <div class="product-image">
                                <a href="<?php echo e(route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug])); ?>" class="text-decoration-none">
                                    <img src="<?php echo e($relatedProduct->thumbnail ? asset('storage/' . $relatedProduct->thumbnail) : asset('img/logo.png')); ?>"
                                        alt="<?php echo e($relatedProduct->name); ?>" loading="lazy">
                                </a>
                                <div class="product-badges">
                                    <?php if($relatedProduct->created_at->diffInDays(now()) <= 7): ?>
                                        <span class="product-badge badge-new">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="<?php echo e(route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug])); ?>" class="text-decoration-none">
                                        <?php echo e($relatedProduct->name); ?>

                                    </a>
                                </h3>

                                <?php if($relatedProduct->description?->description): ?>
                                    <p class="product-description">
                                        <?php echo e(Str::limit($relatedProduct->description->description, 60)); ?>

                                    </p>
                                <?php endif; ?>

                                <div class="product-prices">
                                    <span class="product-price">Tsh <?php echo e(number_format((float) $relatedProduct->new_price, 0)); ?></span>
                                    <?php if($relatedProduct->old_price && $relatedProduct->old_price > $relatedProduct->new_price): ?>
                                        <span class="product-old-price">Tsh <?php echo e(number_format((float) $relatedProduct->old_price, 0)); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-rating">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($relatedProduct->rate > 0): ?>
                                                <i class="bi <?php echo e($i <= round($relatedProduct->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star star text-secondary"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?php echo e(number_format((float) $relatedProduct->rate, 1)); ?>)</span>
                                    <span class="stock-status <?php echo e($relatedProduct->stock > 10 ? 'stock-in' : ($relatedProduct->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                        <?php if($relatedProduct->stock > 10): ?>
                                            In Stock: <?php echo e($relatedProduct->stock); ?>

                                        <?php elseif($relatedProduct->stock > 0): ?>
                                            In Stock: <?php echo e($relatedProduct->stock); ?>

                                        <?php else: ?>
                                            Out of Stock
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="product-meta">
                                    <span class="category">
                                        <i class="bi bi-tag-fill"></i> <?php echo e($relatedProduct->category->name ?? 'Uncategorized'); ?>

                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rating-modal-content">
                <div class="modal-header rating-modal-header">
                    <h5 class="modal-title rating-modal-title" id="ratingModalLabel">
                        <i class="bi bi-star-fill"></i>
                        <span>Rate this Product</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('shop.rate', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Your Rating</label>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?php echo e($i); ?>" name="rating"
                                        value="<?php echo e($i); ?>" class="d-none" required>
                                    <label for="star<?php echo e($i); ?>" class="bi bi-star-fill star-rating"></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review" class="form-label">Your Review (Optional)</label>
                            <textarea class="form-control" id="review" name="review" rows="3"
                                placeholder="Share your thoughts about this product..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn rating-submit-btn" id="submitRatingBtn">
                            <i class="bi bi-send"></i> Submit Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
        $currentUserRole = auth()->check() ? auth()->user()->role : null;
        $currentUserDashboardUrl = route('login');
        if (auth()->check()) {
            $currentUserDashboardUrl = match (auth()->user()->role) {
                'admin' => route('admin.dashboard'),
                'seller' => route('seller.dashboard'),
                default => route('customer.dashboard'),
            };
        }
    ?>
    <script>
        window.productId = <?php echo e($product->id); ?>;
        window.productPublicId = <?php echo json_encode($product->public_id, 15, 512) ?>;
        window.productSlug = <?php echo json_encode($product->slug, 15, 512) ?>;
        window.productViewActivityUrl = <?php echo json_encode(route('shop.view.activity', ['public_id' => $product->public_id, 'slug' => $product->slug])) ?>;
        window.currentUserRole = <?php echo json_encode($currentUserRole, 15, 512) ?>;
        window.currentUserEmailVerified = <?php echo json_encode(auth()->check() && auth()->user()->hasVerifiedEmail(), 15, 512) ?>;
        window.currentUserDashboardUrl = <?php echo json_encode($currentUserDashboardUrl, 15, 512) ?>;
    </script>

    
<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/show.js')); ?>"></script>
    <script src="<?php echo e(asset('js/show-rating.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/shop/show.blade.php ENDPATH**/ ?>