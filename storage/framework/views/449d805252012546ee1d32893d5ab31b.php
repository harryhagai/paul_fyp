<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'Kids Shop'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//code.jquery.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">

    <link href="<?php echo e(asset('css/sellerHeader.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/sellerSidebar.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/sellerLayout.css')); ?>" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        <?php
            $systemColors = \App\Models\SiteSetting::whereIn('key', ['theme_primary_color', 'theme_secondary_color', 'theme_bg_color'])->pluck('value', 'key');
        ?>
        :root {
            <?php if(isset($systemColors['theme_primary_color'])): ?>
            --teal-primary: <?php echo e($systemColors['theme_primary_color']); ?>;
            <?php endif; ?>
            <?php if(isset($systemColors['theme_secondary_color'])): ?>
            --teal-secondary: <?php echo e($systemColors['theme_secondary_color']); ?>;
            --teal-light: <?php echo e($systemColors['theme_secondary_color']); ?>;
            <?php endif; ?>
            <?php if(isset($systemColors['theme_bg_color'])): ?>
            --teal-bg: <?php echo e($systemColors['theme_bg_color']); ?>;
            <?php endif; ?>
        }
    </style>

    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body class="seller-body">
    <?php echo $__env->make('components.dashboardHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.dashboardSidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div id="seller-main">
        <main id="seller-content" class="pb-5">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo e(asset('js/sellerSidebarToggler.js')); ?>"></script>
    <script src="<?php echo e(asset('js/buttonSpinner.js')); ?>"></script>
    <script src="<?php echo e(asset('js/sidebarMenuSpinner.js')); ?>"></script>

    <?php echo $__env->yieldContent('scripts'); ?>

    <?php if(session('success')): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo e(session('success')); ?>',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo e(session('error')); ?>',
                showConfirmButton: true
            });
        </script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\Users\hngob\hidaya_fyp\resources\views/layouts/dashboard.blade.php ENDPATH**/ ?>