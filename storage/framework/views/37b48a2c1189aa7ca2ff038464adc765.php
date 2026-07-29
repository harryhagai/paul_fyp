


<?php $__env->startSection('title', 'Register'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
?>
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-11 col-xl-10">
                <div class="card auth-bs-card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-12 col-lg-6 d-flex">
                            <aside class="auth-login-brand-panel h-100">
                                <div class="auth-login-brand">
                                    <img
                                        src="<?php echo e(isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png')); ?>"
                                        alt="<?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?> Logo">
                                    <span>
                                        <strong><?php echo e($headerSettings['header_school_name'] ?? 'KidsStore'); ?></strong>
                                        <small><?php echo e($headerSettings['header_school_subtitle'] ?? 'Premium Kids Shopping Experience'); ?></small>
                                    </span>
                                </div>

                                <div class="auth-login-pill">Create Account</div>

                                <div class="auth-login-copy">
                                    <h1>Join Us Today</h1>
                                    <p>Create your account to enjoy a faster, secure, and personalized shopping experience.</p>
                                </div>

                                <div class="auth-login-guide">
                                    <h3>Before you register</h3>
                                    <ul>
                                        <li>Use your real name and active email address.</li>
                                        <li>Add a valid phone number you can access.</li>
                                        <li>Choose a strong password to protect your account.</li>
                                    </ul>
                                </div>

                                <div class="auth-login-watermark" aria-hidden="true">
                                    <i class="bi bi-person-check"></i>
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
                                        <i class="bi bi-person-plus"></i>
                                        <span>Register</span>
                                    </h2>

                                    <p class="auth-login-subtitle">Fill in your details to create an account.</p>

                                    <?php if($errors->any()): ?>
                                        <div class="alert alert-danger auth-alert"><?php echo e($errors->first()); ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="<?php echo e(route('register')); ?>" class="auth-form auth-login-form">
                                        <?php echo csrf_field(); ?>

                                        <div class="auth-input-group">
                                            <label for="name">Full Name</label>
                                            <div class="auth-input-wrap <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <i class="bi bi-person auth-input-icon"></i>
                                                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Your full name" required autocomplete="name" autofocus>
                                            </div>
                                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="auth-field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

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
                                                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="name@example.com" required autocomplete="email">
                                            </div>
                                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="auth-field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <div class="auth-input-group">
                                            <label for="phone_number">Phone Number</label>
                                            <div class="auth-input-wrap <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <i class="bi bi-telephone auth-input-icon"></i>
                                                <input id="phone_number" type="text" name="phone_number" value="<?php echo e(old('phone_number')); ?>" placeholder="e.g. 255712345678" required autocomplete="tel">
                                            </div>
                                            <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="auth-field-error"><?php echo e($message); ?></div><?php unset($message);
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
                                                <input id="password" type="password" name="password" placeholder="Create password" required autocomplete="new-password">
                                            </div>
                                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="auth-field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <button type="submit" class="btn btn-brand auth-login-button">
                                            <i class="bi bi-person-plus"></i>
                                            <span>Create Account</span>
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
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\auth\register.blade.php ENDPATH**/ ?>