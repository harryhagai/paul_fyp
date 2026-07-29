


<?php $__env->startSection('title', 'Server Error - ' . config('app.name', 'TotoNest')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Error Illustration -->
                <div class="error-illustration mb-4">
                    <i class="bi bi-tools error-icon" style="font-size: 8rem;"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold error-code mb-3">500</h1>

                <!-- Error Title -->
                <h2 class="h3 mb-3">Internal Server Error</h2>

                <!-- Error Message -->
                <p class="lead text-muted mb-4">
                    Oops! Something went wrong on our end. Our team has been notified
                    and we're working to fix this issue as quickly as possible.
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <button onclick="location.reload()" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Try Again
                    </button>
                    <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>
                        Go to Shop
                    </a>
                </div>

                <!-- Status Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-activity me-2 status-icon"></i>
                            System Status
                        </h5>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="status-indicator status-error me-2"></div>
                            <span class="text-muted">We're experiencing technical difficulties</span>
                        </div>
                        <p class="text-muted mb-3">
                            This is usually temporary and should be resolved soon.
                            Please check back in a few minutes.
                        </p>
                        <div class="alert alert-warning border-0" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>What you can do:</strong> Try refreshing the page or come back later.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    opacity: 0.7;
    animation: bounce 2s ease-in-out infinite;
}

.error-icon,
.error-code,
.status-icon {
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

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-error {
    background-color: var(--teal-primary, #0d6efd);
    box-shadow: 0 0 10px color-mix(in srgb, var(--teal-primary, #0d6efd) 45%, transparent);
    animation: pulse-theme 2s ease-in-out infinite;
}

@keyframes pulse-theme {
    0%, 100% { box-shadow: 0 0 10px color-mix(in srgb, var(--teal-primary, #0d6efd) 45%, transparent); }
    50% { box-shadow: 0 0 20px color-mix(in srgb, var(--teal-primary, #0d6efd) 75%, transparent); }
}

.card {
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--teal-secondary, #20c997) 14%, white) 0%,
        var(--teal-bg, #ffffff) 100%
    );
    border-radius: 15px;
}

.alert-warning {
    background: color-mix(in srgb, var(--teal-secondary, #20c997) 14%, white);
    border: none;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\errors\500.blade.php ENDPATH**/ ?>