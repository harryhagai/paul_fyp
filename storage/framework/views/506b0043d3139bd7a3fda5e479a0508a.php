


<?php $__env->startSection('title', 'Page Not Found - ' . config('app.name', 'TotoNest')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Error Illustration -->
                <div class="error-illustration mb-4">
                    <i class="bi bi-search error-icon" style="font-size: 8rem;"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold error-code mb-3">404</h1>

                <!-- Error Title -->
                <h2 class="h3 mb-3">Oops! Page Not Found</h2>

                <!-- Error Message -->
                <p class="lead text-muted mb-4">
                    The page you're looking for doesn't exist or has been moved.
                    Don't worry, let's get you back on track!
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <a href="<?php echo e(route('shop')); ?>" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>
                        Go to Shop
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-arrow-left me-2"></i>
                        Go Back
                    </button>
                </div>

                <!-- Search Suggestion -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-search me-2 text-primary"></i>
                            Try searching for what you need:
                        </h5>
                        <form class="d-flex" action="<?php echo e(route('shop')); ?>" method="GET">
                            <input type="search" name="search" class="form-control form-control-lg me-2"
                                   placeholder="Search for toys, games, books..." required>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-illustration {
    opacity: 0.6;
    animation: float 3s ease-in-out infinite;
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

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.card {
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--teal-secondary, #20c997) 12%, white) 0%,
        var(--teal-bg, #ffffff) 100%
    );
    border-radius: 15px;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\errors\404.blade.php ENDPATH**/ ?>