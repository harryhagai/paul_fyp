


<?php $__env->startSection('title', 'Shopping Cart - KidsStore'); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/cart.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
        $headerLogo = isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png');
        $hasOutOfStockItems = $cartItems->contains(fn ($item) => (int) $item->product->stock <= 0);
    ?>

    <main class="shop-container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <section class="cart-page-header mb-4">
                    <div class="cart-header-start">
                        <a href="<?php echo e(route('shop')); ?>" class="cart-header-logo" aria-label="Go to shop home">
                            <img src="<?php echo e($headerLogo); ?>" alt="KidsStore Logo">
                        </a>
                        <h1 class="cart-title mb-0">My Shopping Cart</h1>
                    </div>
                    <p class="cart-subtitle mb-0">
                        <?php echo e($cartItems->count() > 0 ? $cartItems->count() . ' products • ' . $cartItems->sum('quantity') . ' items in your cart' : 'Your shopping bag is currently empty'); ?>

                    </p>
                    <?php if($cartItems->count() > 0): ?>
                        <a href="<?php echo e(route('shop')); ?>" class="btn btn-theme-outline cart-continue-btn cart-dot-btn" data-spin-link="1">
                            <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-plus-circle"></i> Continue Shopping</span>
                        </a>
                    <?php endif; ?>
                </section>

                <?php if($cartItems->count() > 0): ?>
                    <?php if($hasOutOfStockItems): ?>
                        <div class="alert alert-warning" role="alert">
                            Some products in your cart are out of stock. Remove them before proceeding to order.
                        </div>
                    <?php endif; ?>
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-lg-8 order-1 order-lg-1">
                            <div class="cart-items-grid">
                                <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <article class="cart-item-card" data-cart-item-id="<?php echo e($item->public_id); ?>"
                                        data-product-id="<?php echo e($item->product_id); ?>" data-price="<?php echo e($item->price); ?>"
                                        data-stock="<?php echo e($item->product->stock); ?>">
                                        <div class="cart-item-image-wrap">
                                            <img src="<?php echo e($item->product->thumbnail
                                                ? asset('storage/' . $item->product->thumbnail)
                                                : ($item->product->media->where('is_primary', true)->first()
                                                    ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                    : asset('img/logo.png'))); ?>"
                                                alt="<?php echo e($item->product->name); ?>" class="cart-item-image">
                                        </div>

                                        <div class="cart-item-content">
                                            <h2 class="cart-item-title">
                                                <a href="<?php echo e(route('shop.show', ['public_id' => $item->product->public_id, 'slug' => $item->product->slug])); ?>"><?php echo e($item->product->name); ?></a>
                                            </h2>
                                            <?php if((int) $item->product->stock <= 0): ?>
                                                <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                                    This product is out of stock. Remove it before proceeding to order.
                                                </div>
                                            <?php endif; ?>
                                            <p class="cart-item-price mb-2"><?php echo e(format_money_short($item->price, 2)); ?></p>

                                            <div class="cart-item-controls">
                                                <div class="quantity-control">
                                                    <button class="btn btn-sm quantity-btn" onclick="updateQuantity('<?php echo e($item->public_id); ?>', -1)"
                                                        aria-label="Decrease quantity">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="number" min="1" max="<?php echo e($item->product->stock); ?>"
                                                        value="<?php echo e($item->quantity); ?>" class="form-control form-control-sm quantity-input"
                                                        oninput="previewQuantityChange(this)"
                                                        onchange="validateAndUpdateQuantity(this, '<?php echo e($item->public_id); ?>', <?php echo e($item->product->stock); ?>)"
                                                        >
                                                    <button class="btn btn-sm quantity-btn" onclick="updateQuantity('<?php echo e($item->public_id); ?>', 1)"
                                                        aria-label="Increase quantity">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>

                                                <button class="btn btn-sm btn-outline-danger btn-remove cart-dot-btn" onclick="removeItem('<?php echo e($item->public_id); ?>')"
                                                    data-item-id="<?php echo e($item->public_id); ?>">
                                                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                    <span class="button-text"><i class="bi bi-trash"></i> Remove</span>
                                                </button>
                                            </div>

                                            <div class="cart-item-total">
                                                <span class="cart-item-total-value">Total : <?php echo e(format_money_short($item->price * $item->quantity, 2)); ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 order-2 order-lg-2">
                            <aside class="card cart-summary-card sticky-lg-top" style="top: 1rem;">
                                <div class="card-body">
                                    <h5 class="mb-3">Cart Summary</h5>

                                    <div class="summary-line">
                                        <span class="cart-summary-subtotal">Subtotal (<?php echo e($cartItems->sum('quantity')); ?> items)</span>
                                        <span class="cart-summary-subtotal-amount"><?php echo e(format_money_short($subtotal, 2)); ?></span>
                                    </div>
                                    <hr>

                                    <div class="summary-total">
                                        <span>Total</span>
                                        <span class="cart-summary-total"><?php echo e(format_money_short($total, 2)); ?></span>
                                    </div>

                                    <div class="summary-actions mt-4">
                                        <button class="btn btn-theme-outline-danger w-100 clear-cart-btn cart-dot-btn" onclick="clearCart(this)">
                                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                            <span class="button-text"><i class="bi bi-trash me-1"></i> Clear Cart</span>
                                        </button>
                                        <?php if($hasOutOfStockItems): ?>
                                            <button type="button" class="btn btn-secondary w-100" disabled>
                                                <span class="button-text"><i class="bi bi-exclamation-triangle me-1"></i> Out of Stock Items</span>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('checkout.index')); ?>" class="btn btn-theme w-100 cart-dot-btn" data-spin-link="1">
                                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text"><i class="bi bi-credit-card me-1"></i> Proceed To Order</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <div class="cart-mobile-actions" aria-label="Mobile checkout actions">
                        <button class="btn btn-theme-outline-danger clear-cart-btn cart-dot-btn" onclick="clearCart(this)">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-trash me-1"></i> Clear Cart</span>
                        </button>
                        <?php if($hasOutOfStockItems): ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <span class="button-text"><i class="bi bi-exclamation-triangle me-1"></i> Out of Stock Items</span>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo e(route('checkout.index')); ?>" class="btn btn-theme cart-dot-btn" data-spin-link="1">
                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text"><i class="bi bi-credit-card me-1"></i> Proceed To Order</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <section class="cart-empty-state">
                        <div class="cart-empty-inner">
                            <div class="cart-empty-icon-wrap">
                                <i class="bi bi-bag-x"></i>
                            </div>
                            <p class="cart-empty-kicker mb-2">No Items Yet</p>
                            <h3>Your cart is empty</h3>
                            <p class="cart-empty-copy">Looks like you haven't added any items yet. Explore products and start building your order.</p>
                            <a href="<?php echo e(route('shop')); ?>" class="btn btn-theme cart-empty-cta cart-dot-btn" data-spin-link="1">
                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text"><i class="bi bi-grid me-1"></i> Start Shopping</span>
                            </a>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/cart.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\shop\cart.blade.php ENDPATH**/ ?>