
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'TotoNest')); ?></title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    
    <link href="<?php echo e(asset('css/header.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/footer.css')); ?>" rel="stylesheet">

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
        
        body {
            margin: 0;
            padding: 0;
        }

        /* hero spacing handled by home.css */

        .category-delete-popup {
            border-radius: 24px;
            padding: 1.75rem 1.5rem 1.25rem;
            overflow: hidden;
            position: relative;
        }

        .category-delete-popup .swal2-timer-progress-bar-container {
            left: 0;
            right: 0;
            bottom: 0;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .category-delete-title {
            color: #4b5563;
            font-weight: 500;
        }

        .category-delete-text {
            color: #6b7280;
        }

        .category-delete-confirm,
        .category-delete-cancel {
            background: transparent;
            border: 1px solid;
            border-radius: 12px;
            padding: 0.7rem 1.6rem;
            font-weight: 500;
            line-height: 1;
        }

        .category-delete-confirm {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
            background: #fff;
        }

        .category-delete-confirm:hover {
            background: var(--teal-primary, #0d9488);
            color: #fff;
        }

        .category-delete-confirm:focus,
        .category-delete-confirm:focus-visible {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal-primary, #0d9488) 22%, transparent);
            outline: none;
        }

        .category-delete-cancel {
            border-color: #9ca3af;
            color: #6b7280;
        }

        .category-delete-cancel:hover {
            background: #6b7280;
            color: #fff;
        }
    </style>
    
    <?php echo $__env->yieldContent('css'); ?>
</head>

<body>

    
    <?php echo $__env->make('components.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
  <main>
    <?php echo $__env->yieldContent('content'); ?>
</main>


    
    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    
    <script src="<?php echo e(asset('js/header.js')); ?>"></script>
    <script src="<?php echo e(asset('js/buttonSpinner.js')); ?>"></script>

    <?php if(auth()->guard()->check()): ?>
    
    
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
        <?php echo csrf_field(); ?>
    </form>
    <?php endif; ?>

    <?php if(session('success')): ?>
    <?php
        $successMessage = (string) session('success');
        $normalizedSuccess = \Illuminate\Support\Str::lower($successMessage);
        $isCartSuccess = \Illuminate\Support\Str::contains($normalizedSuccess, 'cart');
    ?>
    <script>
        const successMessage = <?php echo json_encode($successMessage, 15, 512) ?>;
        const isCartSuccess = <?php echo json_encode($isCartSuccess, 15, 512) ?>;

        const baseOptions = {
            text: successMessage,
            icon: 'success',
            color: '#4b5563',
            background: '#ffffff',
            confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--teal-primary').trim() || '#0d9488',
            showCloseButton: true,
            timer: 3500,
            timerProgressBar: true,
            customClass: {
                popup: 'category-delete-popup',
                title: 'category-delete-title',
                htmlContainer: 'category-delete-text',
                actions: 'category-delete-actions',
                confirmButton: 'category-delete-confirm',
                cancelButton: 'category-delete-cancel',
            },
            buttonsStyling: false
        };

        if (isCartSuccess) {
            Swal.fire({
                ...baseOptions,
                title: 'Item Added to Cart!',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-cart3 me-1"></i>View Cart',
                showCancelButton: true,
                cancelButtonText: '<i class="bi bi-arrow-left-right me-1"></i>Continue Shopping',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/cart';
                }
            });
        } else {
            Swal.fire({
                ...baseOptions,
                title: 'Success',
                confirmButtonText: 'OK'
            });
        }
    </script>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?php echo json_encode(session('error'), 15, 512) ?>,
            showConfirmButton: true
        });
    </script>
    <?php endif; ?>

    
    <?php echo $__env->yieldContent('scripts'); ?>

</body>

</html>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\layouts\app.blade.php ENDPATH**/ ?>