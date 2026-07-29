
<?php
    $authUser = Auth::user();
    $displayName = $authUser?->name ?: 'User';
    $displayEmail = $authUser?->email ?: '';
    $currentRole = strtolower((string) ($authUser?->role ?? ''));
    $roleLabel = $currentRole ? ucfirst($currentRole) : 'User';
    $profilePhoto = $authUser?->profile_photo;
    $currentPageLabel = 'Dashboard';
    $pathMap = [
        'admin/dashboard*' => 'Admin Dashboard',
        'admin/settings*' => 'System Settings',
        'seller/dashboard*' => 'Dashboard',
        'seller/products*' => 'Products',
        'seller/categories*' => 'Categories',
        'seller/orders*' => 'Orders',
        'seller/customers*' => 'Customers',
        'seller/notifications*' => 'Notifications',
        'seller/settings*' => 'Settings',
        'seller/profile*' => 'My Profile',
        'customer/dashboard*' => 'Dashboard',
        'customer/orders*' => 'My Orders',
        'customer/order*' => 'Order Details',
        'customer/addresses*' => 'Addresses',
        'customer/profile*' => 'My Profile',
    ];

    foreach ($pathMap as $pattern => $label) {
        if (request()->is($pattern)) {
            $currentPageLabel = $label;
            break;
        }
    }

    $unreadQuery = \App\Models\Notification::where(function ($query) {
        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
    })->unread();

    if ($currentRole === 'customer') {
        $unreadQuery->where('user_id', $authUser?->id);
    }

    $unreadCount = $unreadQuery->count();
    $recentNotifications = (clone $unreadQuery)->latest()->take(6)->get();
    $notificationsIndexRoute = match ($currentRole) {
        'seller' => route('seller.notifications.index'),
        'customer' => route('customer.dashboard'),
        'admin' => route('admin.dashboard'),
        default => route('shop'),
    };
    $profileRoute = match ($currentRole) {
        'seller' => route('seller.profile'),
        'customer' => route('customer.profile'),
        'admin' => route('admin.profile'),
        default => route('shop'),
    };
    $themePrimary = \App\Models\SiteSetting::where('key', 'theme_primary_color')->value('value') ?? '#0d9488';
    $avatarBg = ltrim($themePrimary, '#');
?>

<header id="main-header">
    <div class="d-flex align-items-center header-page-wrap">
        <button id="sidebarToggle" class="btn btn-outline-secondary me-3" type="button" aria-label="Toggle sidebar" aria-expanded="true">
            <i id="sidebarToggleIcon" class="bi bi-layout-sidebar-inset fs-5"></i>
        </button>

        <div class="header-page-pill">
            <span class="header-page-dot" aria-hidden="true"></span>
            <span class="header-page-label"><?php echo e($currentPageLabel); ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-light border rounded-circle position-relative shadow-sm header-action-btn"
                type="button" id="sellerNotificationDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                aria-expanded="false" title="Notifications">
                <i class="bi bi-bell fs-5 header-notification-icon position-absolute top-50 start-50 translate-middle"></i>
                <?php if($unreadCount > 0): ?>
                    <span class="notification-badge badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"><?php echo e($unreadCount > 99 ? '99+' : $unreadCount); ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0 shadow-sm seller-notification-menu" aria-labelledby="sellerNotificationDropdown">
                <div class="notification-menu-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                    <div>
                        <div class="fw-normal">Notifications</div>
                        <div class="text-muted small">Latest updates</div>
                    </div>
                    <a href="<?php echo e($notificationsIndexRoute); ?>" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="seller-header-notification-list">
                    <?php $__empty_1 = true; $__currentLoopData = $recentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $notificationRouteKey = $notification->public_id ?: $notification->id;
                        ?>
                        <a href="<?php echo e($currentRole === 'seller' ? route('seller.notifications.show', $notificationRouteKey) : $notificationsIndexRoute); ?>" class="seller-header-notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>">
                            <div class="seller-header-notification-item-title"><?php echo e($notification->title); ?></div>
                            <div class="seller-header-notification-item-message"><?php echo e(\Illuminate\Support\Str::limit($notification->message, 70)); ?></div>
                            <div class="seller-header-notification-item-time"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="seller-header-notification-empty text-muted small">No notifications yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="dropdown seller-profile-dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="sellerProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if($profilePhoto): ?>
                    <img src="<?php echo e(asset('storage/profile_photos/' . $profilePhoto)); ?>" alt="<?php echo e($displayName); ?> profile picture" class="profile-avatar" />
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($displayName)); ?>&background=<?php echo e($avatarBg); ?>&color=fff&size=40" alt="<?php echo e($displayName); ?> profile picture" class="profile-avatar" />
                <?php endif; ?>
                <span class="ms-2 d-none d-md-inline fw-normal seller-profile-name"><?php echo e($displayName); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end seller-profile-menu shadow-sm" aria-labelledby="sellerProfileDropdown">
                <li class="seller-profile-menu-header">
                    <div class="d-flex align-items-center gap-2">
                        <?php if($profilePhoto): ?>
                            <img src="<?php echo e(asset('storage/profile_photos/' . $profilePhoto)); ?>" alt="<?php echo e($displayName); ?> profile picture" class="profile-avatar" />
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($displayName)); ?>&background=<?php echo e($avatarBg); ?>&color=fff&size=40" alt="<?php echo e($displayName); ?> profile picture" class="profile-avatar" />
                        <?php endif; ?>
                        <div class="min-w-0">
                            <div class="seller-profile-menu-name"><?php echo e($displayName); ?></div>
                            <?php if($displayEmail): ?>
                                <div class="seller-profile-menu-email"><?php echo e($displayEmail); ?></div>
                            <?php endif; ?>
                            <div class="seller-profile-menu-role"><?php echo e($roleLabel); ?></div>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item seller-profile-menu-item" href="<?php echo e($profileRoute); ?>">
                        <i class="bi bi-person-fill seller-profile-menu-icon profile"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item seller-profile-menu-item">
                            <i class="bi bi-box-arrow-right seller-profile-menu-icon logout"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\components\dashboardHeader.blade.php ENDPATH**/ ?>