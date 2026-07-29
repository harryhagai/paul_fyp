<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Account'); ?> - KidsStore</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="<?php echo e(asset('css/auth.css')); ?>" rel="stylesheet">
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
<body>
    <main class="auth-shell">
        <div class="auth-bg-shape auth-bg-shape-1"></div>
        <div class="auth-bg-shape auth-bg-shape-2"></div>
        <div class="container">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('js/buttonSpinner.js')); ?>"></script>
    <script src="<?php echo e(asset('js/authSpinner.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\hngob\hidaya_fyp\resources\views/layouts/auth.blade.php ENDPATH**/ ?>