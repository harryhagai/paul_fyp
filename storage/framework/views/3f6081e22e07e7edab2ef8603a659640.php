


<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-layout-text-window-reverse me-3"></i>Header Settings</h1>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <?php echo $__env->make('admin.settings._nav', ['active' => 'header'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="panel-card-head">
                    <h5 class="panel-card-title">Manage Header Content</h5>
                </div>
                <div class="panel-card-body">
                    <form action="<?php echo e(route((auth()->user()->role === 'admin' ? 'admin' : 'seller') . '.settings.header.update')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Brand Name</label>
                                <input type="text" class="form-control" name="header_school_name" value="<?php echo e($settings['header_school_name'] ?? 'KidsStore'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand Subtitle</label>
                                <input type="text" class="form-control" name="header_school_subtitle" value="<?php echo e($settings['header_school_subtitle'] ?? 'Cute  Fun  Safe for Every Child'); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Main Logo Image</label>
                                <input type="file" class="form-control" name="header_logo" accept="image/*">
                                <small class="text-muted d-block mt-2">Leave empty to keep current logo.</small>
                            </div>
                            <?php
                                $headerLogo = $settings['header_logo'] ?? null;
                                $headerLogoUrl = $headerLogo
                                    ? (\Illuminate\Support\Str::startsWith($headerLogo, ['http://', 'https://'])
                                        ? $headerLogo
                                        : asset(ltrim($headerLogo, '/')))
                                    : null;
                            ?>
                            <?php if($headerLogoUrl): ?>
                                <div class="col-12">
                                    <img src="<?php echo e($headerLogoUrl); ?>" alt="Current Logo" height="60" class="rounded border p-1 bg-light">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Header</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/header.blade.php ENDPATH**/ ?>