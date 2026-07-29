


<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-clock-history me-3"></i>Order Auto-Cancel Timer</h1>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <?php echo $__env->make('admin.settings._nav', ['active' => 'orders'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="panel-card-head"><h5 class="panel-card-title">Auto-Cancel Pending Orders</h5></div>
                <div class="panel-card-body">
                    <form action="<?php echo e(route('admin.settings.orders.update')); ?>" method="POST" novalidate>
                        <?php echo csrf_field(); ?>
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo e($errors->first()); ?>

                            </div>
                        <?php endif; ?>
                        <p class="section-note">Set how long a pending order should wait before being automatically cancelled. The default is 24 hours if all fields are 0.</p>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Hours</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="order_auto_cancel_hours" id="order_auto_cancel_hours" value="<?php echo e($settings['order_auto_cancel_hours'] ?? '24'); ?>" min="0" max="720">
                                    <span class="input-group-text">hours</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Minutes</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="order_auto_cancel_minutes" id="order_auto_cancel_minutes" value="<?php echo e($settings['order_auto_cancel_minutes'] ?? '0'); ?>" min="0" max="59">
                                    <span class="input-group-text">min</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Seconds</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="order_auto_cancel_seconds" id="order_auto_cancel_seconds" value="<?php echo e($settings['order_auto_cancel_seconds'] ?? '0'); ?>" min="0" max="59">
                                    <span class="input-group-text">sec</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                                <div>
                                    <strong>Current setting:</strong>
                                    <span id="timer-display">
                                        <?php echo e(($settings['order_auto_cancel_hours'] ?? '24')); ?> hours,
                                        <?php echo e(($settings['order_auto_cancel_minutes'] ?? '0')); ?> minutes,
                                        <?php echo e(($settings['order_auto_cancel_seconds'] ?? '0')); ?> seconds
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Timer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var hours = document.getElementById('order_auto_cancel_hours');
        var minutes = document.getElementById('order_auto_cancel_minutes');
        var seconds = document.getElementById('order_auto_cancel_seconds');
        var display = document.getElementById('timer-display');

        function updateDisplay() {
            var h = parseInt(hours.value) || 0;
            var m = parseInt(minutes.value) || 0;
            var s = parseInt(seconds.value) || 0;
            display.textContent = h + ' hours, ' + m + ' minutes, ' + s + ' seconds';
        }

        hours.addEventListener('input', updateDisplay);
        minutes.addEventListener('input', updateDisplay);
        seconds.addEventListener('input', updateDisplay);
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/orders.blade.php ENDPATH**/ ?>