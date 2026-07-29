


<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-layout-text-window me-3"></i>Footer Settings</h1>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <?php echo $__env->make('admin.settings._nav', ['active' => 'footer'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="panel-card-head"><h5 class="panel-card-title">Manage Footer Content</h5></div>
                <div class="panel-card-body">
                    <form id="footer-settings-form" action="<?php echo e(route((auth()->user()->role === 'admin' ? 'admin' : 'seller') . '.settings.footer.update')); ?>" method="POST" novalidate>
                        <?php echo csrf_field(); ?>
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo e($errors->first()); ?>

                            </div>
                        <?php endif; ?>
                        <p class="section-note">Brand, contact details and social links shown in footer.</p>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Footer Brand Name</label><input type="text" class="form-control" name="footer_brand_name" value="<?php echo e($settings['footer_brand_name'] ?? 'myKidsShop365'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">Footer Brand Subtitle</label><input type="text" class="form-control" name="footer_brand_subtitle" value="<?php echo e($settings['footer_brand_subtitle'] ?? 'Everything Cute for Little Ones'); ?>"></div>
                            <div class="col-12"><label class="form-label">Footer Description</label><textarea class="form-control" name="footer_description" rows="3"><?php echo e($settings['footer_description'] ?? 'Your #1 trusted store for baby clothes, toys, accessories, maternity items, and every adorable thing your child deserves.'); ?></textarea></div>
                            <div class="col-12"><label class="form-label">Physical Address</label><input type="text" class="form-control" name="footer_contact_address" value="<?php echo e($settings['footer_contact_address'] ?? 'Kids Plaza, Dar es Salaam, Tanzania'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" class="form-control" name="footer_contact_phone" value="<?php echo e($settings['footer_contact_phone'] ?? '+255 712 345 678'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" class="form-control" name="footer_contact_email" value="<?php echo e($settings['footer_contact_email'] ?? 'support@mykidsshop365.com'); ?>"></div>
                            <div class="col-12"><label class="form-label">Working Hours</label><input type="text" class="form-control" name="footer_contact_hours" value="<?php echo e($settings['footer_contact_hours'] ?? 'Mon - Sat: 9:00 AM - 7:00 PM'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">Facebook URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_facebook" value="<?php echo e($settings['footer_social_facebook'] ?? '#'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">Instagram URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_instagram" value="<?php echo e($settings['footer_social_instagram'] ?? '#'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">TikTok URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_tiktok" value="<?php echo e($settings['footer_social_tiktok'] ?? '#'); ?>"></div>
                            <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_youtube" value="<?php echo e($settings['footer_social_youtube'] ?? '#'); ?>"></div>
                            <div class="col-12"><label class="form-label">Copyright Text</label><input type="text" class="form-control" name="footer_copyright" value="<?php echo e($settings['footer_copyright'] ?? '� 2025 myKidsShop365. All rights reserved.'); ?>"></div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Footer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('footer-settings-form')?.addEventListener('submit', function () {
        this.querySelectorAll('input[name]:not([type="hidden"]), textarea[name], select[name]').forEach((field) => {
            if (field.value === field.defaultValue) {
                field.disabled = true;
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/footer.blade.php ENDPATH**/ ?>