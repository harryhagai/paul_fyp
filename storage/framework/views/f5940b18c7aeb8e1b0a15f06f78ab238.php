
<?php
    $settingsRoutePrefix = auth()->check() && auth()->user()->role === 'admin' ? 'admin' : 'seller';
?>
<div class="card panel-card settings-page-tabs-wrap">
    <div class="panel-card-body p-2">
        <ul class="nav settings-page-tabs">
            <li class="nav-item">
                <a href="<?php echo e(route($settingsRoutePrefix . '.settings.header')); ?>" class="settings-nav-link <?php echo e(($active ?? '') === 'header' ? 'active' : ''); ?>">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    <span>Header</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route($settingsRoutePrefix . '.settings.footer')); ?>" class="settings-nav-link <?php echo e(($active ?? '') === 'footer' ? 'active' : ''); ?>">
                    <i class="bi bi-layout-text-window"></i>
                    <span>Footer</span>
                </a>
            </li>
            <li class="nav-item">
                <?php if($settingsRoutePrefix === 'admin'): ?>
                <a href="<?php echo e(route('admin.settings.mail')); ?>" class="settings-nav-link <?php echo e(($active ?? '') === 'mail' ? 'active' : ''); ?>">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Mail</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route($settingsRoutePrefix . '.settings.orders')); ?>" class="settings-nav-link <?php echo e(($active ?? '') === 'orders' ? 'active' : ''); ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Orders</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/_nav.blade.php ENDPATH**/ ?>