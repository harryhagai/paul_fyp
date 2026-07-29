


<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-11 col-xl-10">
                <div class="card auth-bs-card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-12 col-lg-6 d-flex">
                            <aside class="auth-login-brand-panel h-100">
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

                                <div class="auth-login-pill">Secure Access</div>

                                <div class="auth-login-copy">
                                    <h1>Welcome Back</h1>
                                    <p>Sign in to continue with secure access to your KidsStore services.</p>
                                </div>

                                <div class="auth-login-guide">
                                    <h3>How to fill this form</h3>
                                    <ul>
                                        <li>Use your registered email address and password.</li>
                                        <li>Keep your account active with protected sessions.</li>
                                        <li>Reset your password quickly if you lose access.</li>
                                    </ul>
                                </div>

                                <div class="auth-login-watermark" aria-hidden="true">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                            </aside>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="auth-login-form-panel auth-bs-panel">
                                <div class="auth-login-form-card w-100">
                                    <div class="auth-mobile-logo d-lg-none">
                                        <img
                                            src="<?php echo e(isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png')); ?>"
                                            alt="<?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?> Logo">
                                    </div>
                                    <h2 class="auth-login-title mb-2">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        <span>Login</span>
                                    </h2>

                                    <p class="auth-login-subtitle">Enter your credentials to access your account.</p>

                                    <?php if($errors->any()): ?>
                                        <div class="alert alert-danger auth-alert"><?php echo e($errors->first()); ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form auth-login-form">
                                        <?php echo csrf_field(); ?>

                                        <div class="auth-input-group">
                                            <label for="email">Email Address</label>
                                            <div class="auth-input-wrap <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <i class="bi bi-envelope auth-input-icon"></i>
                                                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="name@example.com" required autocomplete="email" autofocus>
                                            </div>
                                            <?php $__errorArgs = ['email'];
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
                                            <label for="password">Password</label>
                                            <div class="auth-input-wrap <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <i class="bi bi-lock auth-input-icon"></i>
                                                <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
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

                                        <div class="auth-form-meta auth-login-meta flex-wrap gap-2">
                                            <label class="auth-checkbox" for="remember">
                                                <input id="remember" type="checkbox" name="remember" value="1" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                                <span>Remember me</span>
                                            </label>
                                            <a href="<?php echo e(route('password.request')); ?>" class="auth-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text">Forgot Password?</span>
                                            </a>
                                        </div>

                                        <button type="submit" class="btn btn-brand auth-login-button" data-no-spinner>
                                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                            <span class="button-text">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Login</span>
                                            </span>
                                        </button>

                                        <div class="auth-login-back-wrap">
                                            <a href="<?php echo e(route('register')); ?>" class="auth-login-back-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text"><span class="auth-inline-hint">Don't have an account? </span><span class="auth-inline-cta">Register</span></span>
                                            </a>
                                        </div>

                                        <div class="auth-login-back-wrap">
                                            <a href="<?php echo e(route('shop')); ?>" class="auth-login-back-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text">
                                                    <i class="bi bi-arrow-left"></i>
                                                    <span>Back to Shop</span>
                                                </span>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/auth/login.blade.php ENDPATH**/ ?>