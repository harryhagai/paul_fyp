


<?php $__env->startSection('title', 'Notification - KidsStore Seller'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('seller.notifications.index')); ?>">Notifications</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo e($notification->title); ?></li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo e($notification->title); ?></h4>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?php echo e($notification->created_at->format('M d, Y H:i')); ?>

                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="notification-content">
                                <p class="lead"><?php echo e($notification->message); ?></p>

                                <?php if($notification->data && count($notification->data) > 0): ?>
                                    <div class="mt-4">
                                        <h6>Additional Details:</h6>
                                        <div class="bg-light p-3 rounded">
                                            <?php $__currentLoopData = $notification->data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-2">
                                                    <strong><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                                                    <?php echo e(is_array($value) ? json_encode($value) : $value); ?>

                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-info-circle text-info"></i> Notification Info
                                    </h6>
                                    <div class="mb-2">
                                        <strong>Type:</strong>
                                        <span class="badge bg-secondary"><?php echo e(ucfirst(str_replace('_', ' ', $notification->type))); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Priority:</strong>
                                        <span class="badge bg-<?php echo e($notification->priority === 'high' ? 'danger' : ($notification->priority === 'medium' ? 'warning' : 'secondary')); ?>"><?php echo e(ucfirst($notification->priority)); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Status:</strong>
                                        <?php if($notification->read_at): ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Unread</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($notification->read_at): ?>
                                        <div class="mb-2">
                                            <strong>Read at:</strong>
                                            <?php echo e($notification->read_at->format('M d, Y H:i')); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-2">
                                        <strong>Created:</strong>
                                        <?php echo e($notification->created_at->format('M d, Y H:i')); ?>

                                    </div>
                                    <?php if($notification->expires_at): ?>
                                        <div class="mb-2">
                                            <strong>Expires:</strong>
                                            <?php echo e($notification->expires_at->format('M d, Y H:i')); ?> (<?php echo e($notification->expires_at->diffForHumans()); ?>)
                                        </div>
                                    <?php endif; ?>
                                    <?php if($notification->action_url): ?>
                                        <div class="mb-2">
                                            <strong>Action:</strong>
                                            <a href="<?php echo e($notification->action_url); ?>" class="btn btn-sm btn-outline-info">Go to Action</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="btn-group-vertical w-100" role="group">
                                    <a href="<?php echo e(route('seller.notifications.index')); ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Notifications
                                    </a>
                                    <?php if(!$notification->read_at): ?>
                                        <button class="btn btn-outline-success" onclick="markAsRead()">
                                            <i class="bi bi-check"></i> Mark as Read
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger" onclick="deleteNotification()">
                                        <i class="bi bi-trash"></i> Delete Notification
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function markAsRead() {
    fetch(`<?php echo e(route('seller.notifications.markAsRead', $notification->public_id)); ?>`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to recover this notification!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?php echo e(route('seller.notifications.destroy', $notification->public_id)); ?>`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(() => {
                Swal.fire(
                    'Deleted!',
                    'The notification has been deleted.',
                    'success'
                ).then(() => {
                    window.location.href = '<?php echo e(route('seller.notifications.index')); ?>';
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'Something went wrong. Please try again.',
                    'error'
                );
            });
        }
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\seller\notifications\show.blade.php ENDPATH**/ ?>