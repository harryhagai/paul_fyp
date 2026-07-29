


<?php $__env->startSection('title', 'Verify Email'); ?>

<?php $__env->startSection('content'); ?>
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-7 col-xl-6">
                <div class="card auth-bs-card border-0 shadow-sm">
                    <div class="auth-login-form-panel auth-bs-panel">
                        <div class="auth-login-form-card w-100 text-center">
                            <div class="auth-mobile-logo d-flex">
                                <img src="<?php echo e(asset('img/logo.png')); ?>" alt="Logo">
                            </div>

                            <h2 class="auth-login-title mb-2 justify-content-center">
                                <i class="bi bi-envelope-check"></i>
                                <span>Verify Your Email</span>
                            </h2>
                            <p class="auth-login-subtitle mb-3">
                                A verification link has been sent to
                                <strong class="d-block mt-1"><?php echo e(auth()->user()->email); ?></strong>
                                Please click the link to activate your account.
                            </p>

                            <?php if(session('status')): ?>
                                <div class="alert alert-success auth-alert"><?php echo e(session('status')); ?></div>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-brand">
                                    <i class="bi bi-send"></i>
                                    <span>Resend Verification Link</span>
                                </button>
                            </form>

                            <div class="auth-login-back-wrap">
                                <a href="<?php echo e(route('shop')); ?>" class="auth-login-back-link">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>Back to Shop</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>