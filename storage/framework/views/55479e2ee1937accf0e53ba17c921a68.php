


<?php $__env->startSection('title', 'Page Expired - ' . config('app.name', 'TotoNest')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Error Illustration -->
                <div class="error-illustration mb-4">
                    <i class="bi bi-clock-history error-icon" style="font-size: 8rem;"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold error-code mb-3">419</h1>

                <!-- Error Title -->
                <h2 class="h3 mb-3">Page Session Expired</h2>

                <!-- Error Message -->
                <p class="lead text-muted mb-4">
                    Your session has expired due to inactivity. For security reasons,
                    please refresh the page and try again.
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <button onclick="location.reload()" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Refresh Page
                    </button>
                    <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>
                        Go to Shop
                    </a>
                </div>

                <!-- Info Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-info-circle me-2 info-icon"></i>
                            Why did this happen?
                        </h5>
                        <ul class="text-start mb-0 text-muted">
                            <li>Your session expired after a period of inactivity</li>
                            <li>You stayed too long on a single page</li>
                            <li>Security measure to protect your account</li>
                        </ul>
                        <hr class="my-3">
                        <p class="text-muted mb-0">
                            <small>Refreshing the page will create a new secure session for you.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    opacity: 0.7;
    animation: pulse 2s ease-in-out infinite;
}

.error-icon,
.error-code,
.info-icon {
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

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.05); opacity: 0.9; }
}

.card {
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--teal-secondary, #20c997) 14%, white) 0%,
        var(--teal-bg, #ffffff) 100%
    );
    border-radius: 15px;
}

.card ul li {
    margin-bottom: 0.5rem;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/errors/419.blade.php ENDPATH**/ ?>