


<?php $__env->startSection('title', 'Service Unavailable - ' . config('app.name', 'TotoNest')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Error Illustration -->
                <div class="error-illustration mb-4">
                    <i class="bi bi-cone-striped error-icon" style="font-size: 8rem;"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold error-code mb-3">503</h1>

                <!-- Error Title -->
                <h2 class="h3 mb-3">Service Temporarily Unavailable</h2>

                <!-- Error Message -->
                <p class="lead text-muted mb-4">
                    We're currently performing maintenance or experiencing high traffic.
                    Our service will be back online shortly. Thank you for your patience!
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <button onclick="location.reload()" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Check Again
                    </button>
                    <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>
                        Browse Later
                    </a>
                </div>

                <!-- Maintenance Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-gear me-2 status-icon"></i>
                            Scheduled Maintenance
                        </h5>
                        <div class="maintenance-schedule mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Estimated downtime:</span>
                                <span class="fw-bold">15-30 minutes</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Expected back online:</span>
                                <span class="fw-bold" id="expected-time">Calculating...</span>
                            </div>
                        </div>
                        <p class="text-muted mb-3">
                            We're working hard to improve your shopping experience.
                            Thank you for your understanding!
                        </p>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">Maintenance Progress: 75% Complete</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    opacity: 0.7;
    animation: rotate 4s linear infinite;
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

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.card {
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--teal-secondary, #20c997) 12%, white) 0%,
        var(--teal-bg, #ffffff) 100%
    );
    border-radius: 15px;
}

.maintenance-schedule {
    background: color-mix(in srgb, var(--teal-secondary, #20c997) 10%, white);
    border-radius: 10px;
    padding: 1rem;
}

.progress {
    border-radius: 4px;
}

.progress-bar {
    background-color: var(--teal-primary, #0d6efd) !important;
    border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate expected back online time
    const now = new Date();
    const expectedTime = new Date(now.getTime() + (30 * 60 * 1000)); // 30 minutes from now
    const timeString = expectedTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    document.getElementById('expected-time').textContent = timeString;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\errors\503.blade.php ENDPATH**/ ?>