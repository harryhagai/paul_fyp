


<?php $__env->startSection('title', 'Access Forbidden - ' . config('app.name', 'TotoNest')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Error Illustration -->
                <div class="error-illustration mb-4">
                    <i class="bi bi-shield-lock error-icon" style="font-size: 8rem;"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold error-code mb-3">403</h1>

                <!-- Error Title -->
                <h2 class="h3 mb-3">Access Forbidden</h2>

                <!-- Error Message -->
                <p class="lead text-muted mb-4">
                    Sorry, you don't have permission to access this page.
                    This area is restricted to authorized users only.
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <a href="<?php echo e(route('shop')); ?>" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>
                        Go to Shop
                    </a>
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('customer.dashboard')); ?>" class="btn btn-outline-primary btn-lg px-4">
                            <i class="bi bi-person-circle me-2"></i>
                            My Account
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-primary btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Login
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Help Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-question-circle me-2 text-primary"></i>
                            Need Help?
                        </h5>
                        <p class="text-muted mb-3">
                            If you believe this is an error, please contact our support team or try logging in with the correct account.
                        </p>
                        <a href="<?php echo e(route('contact')); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-envelope me-2"></i>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    opacity: 0.7;
    animation: shake 2s ease-in-out infinite;
}

.error-icon,
.error-code {
    color: var(--teal-primary, #0d6efd);
}

.btn-primary {
    background-color: var(--teal-primary, #0d6efd);
    border-color: var(--teal-primary, #0d6efd);
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: color-mix(in srgb, var(--teal-primary, #0d6efd) 85%, black);
    border-color: color-mix(in srgb, var(--teal-primary, #0d6efd) 85%, black);
}

.btn-outline-primary {
    color: var(--teal-primary, #0d6efd);
    border-color: var(--teal-primary, #0d6efd);
}

.btn-outline-primary:hover,
.btn-outline-primary:focus {
    color: #fff;
    background-color: var(--teal-primary, #0d6efd);
    border-color: var(--teal-primary, #0d6efd);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.card {
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--teal-secondary, #20c997) 14%, white) 0%,
        var(--teal-bg, #ffffff) 100%
    );
    border-radius: 15px;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/errors/403.blade.php ENDPATH**/ ?>