


<?php $__env->startSection('title', 'Reset Password'); ?>

<?php $__env->startSection('content'); ?>
<section class="auth-page auth-login-page">
    <div class="auth-login-frame">
        <div class="auth-login-shell">
            <aside class="auth-login-brand-panel">
                <?php
                    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
                ?>
                <div class="auth-login-brand">
                    <img
                        src="<?php echo e(isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png')); ?>"
                        alt="<?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?> Logo">
                    <span>
                        <strong><?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?></strong>
                        <small><?php echo e($headerSettings['header_school_subtitle'] ?? 'Premium Kids Shopping Experience'); ?></small>
                    </span>
                </div>

                <div class="auth-login-pill">Password Update</div>

                <div class="auth-login-copy">
                    <h1>Reset Password</h1>
                    <p>Create a new password for your account and continue securely.</p>
                </div>

                <div class="auth-login-guide">
                    <h3>Password tips</h3>
                    <ul>
                        <li>Use at least 8 characters.</li>
                        <li>Include letters and numbers.</li>
                        <li>Confirm the same password before saving.</li>
                    </ul>
                </div>

                <div class="auth-login-watermark" aria-hidden="true">
                    <i class="bi bi-shield-lock"></i>
                </div>
            </aside>

            <div class="auth-login-form-panel">
                <div class="auth-login-form-card">
                    <div class="auth-login-title-row">
                        <h2 class="auth-login-title">
                            <i class="bi bi-key"></i>
                            <span>Set New Password</span>
                        </h2>
                    </div>

                    <p class="auth-login-subtitle">Update your password below to regain account access.</p>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger auth-alert"><?php echo e($errors->first()); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('password.update')); ?>" class="auth-form auth-login-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="email" value="<?php echo e($email); ?>">

                        <div class="auth-input-group">
                            <label for="reset_email">Email Address</label>
                            <div class="auth-input-wrap is-readonly">
                                <i class="bi bi-envelope auth-input-icon"></i>
                                <input id="reset_email" type="email" value="<?php echo e($email); ?>" readonly>
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="password">New Password</label>
                            <div class="auth-input-wrap <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <i class="bi bi-lock auth-input-icon"></i>
                                <input id="password" type="password" name="password" placeholder="Create a strong password" required autocomplete="new-password">
                            </div>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="auth-field-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="auth-input-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <div class="auth-input-wrap <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <i class="bi bi-shield-lock auth-input-icon"></i>
                                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repeat the new password" required autocomplete="new-password">
                            </div>
                            <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="auth-field-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <button type="submit" class="btn-brand auth-login-button">
                            <i class="bi bi-check-circle"></i>
                            <span>Save New Password</span>
                        </button>

                        <div class="auth-login-back-wrap">
                            <a href="<?php echo e(route('login')); ?>" class="auth-login-back-link">
                                <i class="bi bi-arrow-left"></i>
                                <span>Back to Login</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>