


<?php $__env->startSection('title', 'Checkout - KidsStore'); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/checkout.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
        $headerLogo = isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png');
    ?>

    <main class="shop-container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 mb-0 d-flex align-items-center gap-2" style="color: var(--teal-primary, #0d9488);">
                        <img src="<?php echo e($headerLogo); ?>" alt="KidsStore Logo"
                            style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px;">
                        <span>Order Details</span>
                    </h1>
                    <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-secondary checkout-dot-btn checkout-desktop-action" data-spin-link="1">
                        <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                        <span class="button-text"><i class="bi bi-arrow-left"></i> Back to Cart</span>
                    </a>
                </div>

                <form id="checkoutForm" action="<?php echo e(route('checkout.store')); ?>" method="POST" class="checkout-form">
                    <?php echo csrf_field(); ?>

                    <div class="row g-4">
                        <!-- Left Column - Customer Information Forms -->
                        <div class="col-lg-8">
                            <!-- Required Information Form -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Required Information</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Personal Information Section -->
                                    <div class="mb-4">
                                        <h6 class="section-title mb-3">Personal Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">First Name *</label>
                                                <input type="text" name="first_name" class="form-control form-control-lg"
                                                    value="<?php echo e(old('first_name', $savedCheckoutInfo->first_name ?? '')); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Name *</label>
                                                <input type="text" name="last_name" class="form-control form-control-lg"
                                                    value="<?php echo e(old('last_name', $savedCheckoutInfo->last_name ?? '')); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email Address *</label>
                                                <input type="email" name="email" class="form-control form-control-lg"
                                                    value="<?php echo e($user->email ?? old('email')); ?>" required readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone Number *</label>
                                                <input type="text" name="phone_number"
                                                    class="form-control form-control-lg" placeholder="+255XXXXXXXXX"
                                                    value="<?php echo e($checkoutPhoneNumber ?: old('phone_number')); ?>"
                                                    maxlength="13" pattern="^\+255[0-9]{9}$" required readonly>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        id="saveRequiredInformation" name="save_required_information"
                                                        <?php echo e(old('save_required_information', 1) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="saveRequiredInformation">
                                                        Save Required Information for next checkout
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Order Summary -->
                        <div class="col-lg-4">
                            <div class="card order-summary-card">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Products -->
                                    <div class="mb-4" id="orderSummaryProducts">
                                        <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($loop->index < 3): ?>
                                                <div class="product-item">
                                                    <div class="product-item-image-wrap">
                                                        <img src="<?php echo e($item->product->thumbnail
                                                            ? asset('storage/' . $item->product->thumbnail)
                                                            : ($item->product->media->where('is_primary', true)->first()
                                                                ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                                : asset('img/logo.png'))); ?>"
                                                            alt="<?php echo e($item->product->name); ?>">
                                                    </div>
                                                    <div class="product-item-content">
                                                        <h6 class="product-item-title mb-1 text-truncate"><?php echo e($item->product->name); ?></h6>
                                                        <p class="product-item-qty mb-0">Qty: <?php echo e($item->quantity); ?></p>
                                                    </div>
                                                    <div class="product-item-total text-end">
                                                        <strong class="text-primary"><?php echo e(format_money_short($item->price * $item->quantity, 2)); ?></strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        <?php if($cartItems->count() > 3): ?>
                                            <div id="orderSummaryExtra" class="order-summary-extra">
                                                <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($loop->index >= 3): ?>
                                                        <div class="product-item">
                                                            <div class="product-item-image-wrap">
                                                                <img src="<?php echo e($item->product->thumbnail
                                                                    ? asset('storage/' . $item->product->thumbnail)
                                                                    : ($item->product->media->where('is_primary', true)->first()
                                                                        ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                                        : asset('img/logo.png'))); ?>"
                                                                    alt="<?php echo e($item->product->name); ?>">
                                                            </div>
                                                            <div class="product-item-content">
                                                                <h6 class="product-item-title mb-1 text-truncate"><?php echo e($item->product->name); ?></h6>
                                                                <p class="product-item-qty mb-0">Qty: <?php echo e($item->quantity); ?></p>
                                                            </div>
                                                            <div class="product-item-total text-end">
                                                                <strong class="text-primary"><?php echo e(format_money_short($item->price * $item->quantity, 2)); ?></strong>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>

                                            <button type="button" class="btn btn-link p-0 mt-2 summary-toggle-btn" id="orderSummaryToggleBtn"
                                                data-expanded="false" data-hidden-count="<?php echo e($cartItems->count() - 3); ?>">
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                                <span>Show more (<?php echo e($cartItems->count() - 3); ?>)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Order Totals -->
                                    <div class="order-total">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal (<?php echo e($cartItems->sum('quantity')); ?> items)</span>
                                            <span><?php echo e(format_money_short($subtotal, 2)); ?></span>
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-flex justify-content-between">
                                            <strong class="fs-5">Total</strong>
                                            <strong class="total-amount fs-5"><?php echo e(format_money_short($total, 2)); ?></strong>
                                        </div>
                                    </div>

                                    <!-- Place Order Button -->
                                    <button type="button" onclick="confirmOrder()" class="btn btn-primary checkout-primary-btn w-100 mt-4 checkout-dot-btn checkout-desktop-action" id="placeOrderBtn">
                                        <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                        <span class="button-text"><i class="bi bi-credit-card me-2"></i>Place Order</span>
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="checkout-mobile-actions" aria-label="Mobile checkout actions">
            <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-secondary checkout-dot-btn" data-spin-link="1">
                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="button-text"><i class="bi bi-arrow-left me-1"></i> Back to Cart</span>
            </a>
            <button type="button" onclick="confirmOrder()" class="btn btn-primary checkout-primary-btn checkout-dot-btn" id="placeOrderBtnMobile">
                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="button-text"><i class="bi bi-credit-card me-1"></i>Place Order</span>
            </button>
        </div>

        <!-- Loading Overlay (hidden by default) -->
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Processing...</span>
                </div>
                <h5 class="mt-3 text-primary">Processing your order...</h5>
                <p class="text-muted">Please wait while we secure your purchase.</p>
            </div>
        </div>

        <!-- Order Confirmation Modal -->
        <div class="modal fade order-confirmation-modal" id="orderConfirmationModal" tabindex="-1"
            aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4" id="orderConfirmationModalLabel">
                            <span class="confirmation-title">Order Confirmation</span>
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="info-grid" id="confirmationDetails">
                            <!-- Details will be populated by JavaScript -->
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-edit checkout-dot-btn" data-bs-dismiss="modal">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-pencil-square me-2"></i>Review & Edit</span>
                        </button>
                        <button type="button" class="btn btn-confirm checkout-dot-btn" id="confirmOrderBtn">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-check-circle me-2"></i>Confirm & Place Order</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <script>
        // Pass data from Laravel to JavaScript
        window.checkoutData = {
            subtotal: <?php echo e($subtotal); ?>,
            total: <?php echo e($total); ?>,
            itemCount: <?php echo e($cartItems->sum('quantity')); ?>,
            routes: {
                store: '<?php echo e(route('checkout.store')); ?>',
                shop: '<?php echo e(route('shop')); ?>',
                orders: '<?php echo e(route('customer.orders')); ?>'
            }
        };
    </script>


<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/checkout.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\shop\checkout.blade.php ENDPATH**/ ?>