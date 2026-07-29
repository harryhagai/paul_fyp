
<!-- Header Component -->
<?php
    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
    $dashboardRoute = route('customer.dashboard');
    $isDashboardActive = request()->is('customer/dashboard*') || request()->is('seller/dashboard*') || request()->is('admin/dashboard*');

    if (auth()->check()) {
        $dashboardRoute = match (auth()->user()->role) {
            'admin' => route('admin.dashboard'),
            'seller' => route('seller.dashboard'),
            default => route('customer.dashboard'),
        };
    }

    $navLinks = [
        ['href' => '/shop', 'label' => 'Shop', 'icon' => 'bi-bag-heart', 'active' => request()->is('shop*')],
        ['href' => '/categories', 'label' => 'Categories', 'icon' => 'bi-grid', 'active' => request()->is('categories*') || request()->is('category*')],
    ];
?>

<header class="header">
    <div class="particles-container" id="particlesContainer"></div>

    <nav class="navbar navbar-expand-lg header-navbar py-0">
        <div class="container-fluid header-shell">
            <a href="<?php echo e(route('shop')); ?>" class="navbar-brand logo-container me-0">
                <div class="logo-main">
                    <img src="<?php echo e(isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png')); ?>" alt="Kids Shop Logo" class="logo-img">
                </div>
                <div class="logo-text">
                    <span class="school-name"><?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?></span>
                    <span class="school-subtitle"><?php echo e($headerSettings['header_school_subtitle'] ?? 'Cute .Fun . Safe for Every Child'); ?></span>
                </div>
            </a>

            <div class="header-mobile-actions">
                <a href="/cart" class="header-mobile-cart <?php echo e(request()->is('cart*') ? 'active' : ''); ?>" aria-label="Open cart">
                    <i class="bi bi-cart-fill"></i>
                    <span class="cart-count" data-cart-count>0</span>
                </a>

                <button class="navbar-toggler header-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainHeaderNav" aria-controls="mainHeaderNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="header-toggler-line"></span>
                    <span class="header-toggler-line"></span>
                    <span class="header-toggler-line"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse header-collapse" id="mainHeaderNav">
                <ul class="navbar-nav ms-auto align-items-lg-center header-nav-list">
                    <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item">
                            <a href="<?php echo e($link['href']); ?>" class="nav-link header-link fw-normal <?php echo e($link['active'] ? 'active' : ''); ?>">
                                <i class="bi <?php echo e($link['icon']); ?>"></i>
                                <span class="header-link-label"><?php echo e($link['label']); ?></span>
                                <span class="header-link-spinner" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <li class="nav-item">
                        <a href="/cart" class="nav-link header-link fw-normal <?php echo e(request()->is('cart*') ? 'active' : ''); ?>">
                            <i class="bi bi-cart-fill"></i>
                            <span class="header-link-label">Cart</span>
                            <span class="cart-count" data-cart-count>0</span>
                            <span class="header-link-spinner" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </a>
                    </li>

                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a href="<?php echo e($dashboardRoute); ?>" class="nav-link header-link fw-normal <?php echo e($isDashboardActive ? 'active' : ''); ?>">
                                <i class="bi bi-speedometer2"></i>
                                <span class="header-link-label">My Dashboard</span>
                                <span class="header-link-spinner" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(auth()->guard()->guest()): ?>
                        <li class="nav-item">
                            <a href="/login" class="nav-link header-link header-link-accent fw-normal <?php echo e(request()->is('login') ? 'active' : ''); ?>">
                                <i class="bi bi-person-circle"></i>
                                <span class="header-link-label">Login</span>
                                <span class="header-link-spinner" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCartCount() {
        fetch('/api/cart/count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const count = data.count;
                document.querySelectorAll('[data-cart-count]').forEach((cartCountElement) => {
                    cartCountElement.textContent = count;
                    cartCountElement.style.display = count > 0 ? 'inline-flex' : 'none';
                });
            }
        })
        .catch(error => {
            console.error('Error fetching cart count:', error);
        });
    }

    updateCartCount();
    setInterval(updateCartCount, 1000);
});
</script>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/components/header.blade.php ENDPATH**/ ?>