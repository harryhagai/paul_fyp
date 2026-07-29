


<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-envelope-fill me-3"></i>Mail Settings</h1>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <?php echo $__env->make('admin.settings._nav', ['active' => 'mail'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="panel-card-head">
                    <h5 class="panel-card-title">SMTP Configuration For Password Reset Emails</h5>
                </div>
                <div class="panel-card-body">
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
                    <?php endif; ?>

                    <form action="<?php echo e(route((auth()->user()->role === 'admin' ? 'admin' : 'seller') . '.settings.mail.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Mailer</label>
                                <select class="form-select" name="mail_mailer" required>
                                    <option value="smtp" <?php if(($settings['mail_mailer'] ?? old('mail_mailer', 'smtp')) === 'smtp'): echo 'selected'; endif; ?>>SMTP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Host</label>
                                <input type="text" class="form-control" name="mail_host" value="<?php echo e(old('mail_host', $settings['mail_host'] ?? '')); ?>" placeholder="smtp.gmail.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <input type="number" min="1" max="65535" class="form-control" name="mail_port" value="<?php echo e(old('mail_port', $settings['mail_port'] ?? 587)); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="mail_username" value="<?php echo e(old('mail_username', $settings['mail_username'] ?? '')); ?>" placeholder="example@domain.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password / App Password</label>
                                <input type="password" class="form-control" name="mail_password" value="<?php echo e(old('mail_password', $settings['mail_password'] ?? '')); ?>" placeholder="Enter SMTP password">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Encryption</label>
                                <select class="form-select" name="mail_encryption">
                                    <option value="tls" <?php if(($settings['mail_encryption'] ?? old('mail_encryption', 'tls')) === 'tls'): echo 'selected'; endif; ?>>TLS</option>
                                    <option value="ssl" <?php if(($settings['mail_encryption'] ?? old('mail_encryption')) === 'ssl'): echo 'selected'; endif; ?>>SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">From Address</label>
                                <input type="email" class="form-control" name="mail_from_address" value="<?php echo e(old('mail_from_address', $settings['mail_from_address'] ?? '')); ?>" placeholder="no-reply@yourstore.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" name="mail_from_name" value="<?php echo e(old('mail_from_name', $settings['mail_from_name'] ?? 'KidsStore')); ?>" required>
                            </div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Mail Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/mail.blade.php ENDPATH**/ ?>