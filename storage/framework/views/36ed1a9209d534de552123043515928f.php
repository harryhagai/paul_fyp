


<?php $__env->startSection('title', 'Notifications - KidsStore Seller'); ?>

<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/seller-notifications.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid seller-notifications-page"
     id="sellerNotificationsPage"
     data-mark-all-url="<?php echo e(route('seller.notifications.markAllAsRead')); ?>"
     data-base-url="<?php echo e(url('seller/notifications')); ?>"
     data-csrf="<?php echo e(csrf_token()); ?>">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 notifications-page-title"><i class="bi bi-bell me-3"></i>Notifications</h1>
                    <p class="notifications-page-subtitle mb-0">Select a notification from the right panel to view full details.</p>
                </div>
                <?php if($unreadCount > 0): ?>
                    <button class="btn btn-outline-primary themed-outline-btn" id="markAllBtn">
                        <i class="bi bi-check-all me-2"></i>Mark All as Read
                    </button>
                <?php endif; ?>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card notification-detail-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Message Details</h5>
                        </div>
                        <div class="card-body" id="notificationDetailPanel">
                            <?php if($notifications->count() > 0): ?>
                                <?php
                                    $first = $notifications->first();
                                    $firstNotificationKey = $first->public_id ?: $first->id;
                                ?>
                                <div class="notification-detail-wrap" data-current-id="<?php echo e($firstNotificationKey); ?>">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <h5 class="notification-title mb-0"><?php echo e($first->title); ?></h5>
                                        <?php if(!$first->read_at): ?>
                                            <span class="badge notification-badge-new">New</span>
                                        <?php endif; ?>
                                        <span class="badge notification-badge-priority priority-<?php echo e($first->priority); ?>"><?php echo e(ucfirst($first->priority)); ?></span>
                                    </div>
                                    <div class="notification-detail-meta mb-3">
                                        <small class="text-muted d-block"><i class="bi bi-clock me-1"></i><?php echo e($first->created_at->diffForHumans()); ?></small>
                                        <?php if($first->expires_at): ?>
                                            <small class="text-muted d-block"><i class="bi bi-calendar-x me-1"></i>Expires <?php echo e($first->expires_at->diffForHumans()); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-detail-message mb-3"><?php echo e($first->message); ?></div>
                                    <div class="d-flex flex-wrap gap-2" id="notificationDetailActions">
                                        <?php if($first->action_url): ?>
                                            <a href="<?php echo e($first->action_url); ?>" class="btn btn-sm btn-outline-primary themed-outline-btn" id="detailActionBtn"><i class="bi bi-link-45deg me-1"></i>Action</a>
                                        <?php endif; ?>
                                        <?php if(!$first->read_at): ?>
                                            <button class="btn btn-sm btn-outline-primary themed-outline-btn" id="detailMarkReadBtn" data-id="<?php echo e($firstNotificationKey); ?>"><i class="bi bi-check2 me-1"></i>Mark Read</button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" id="detailDeleteBtn" data-id="<?php echo e($firstNotificationKey); ?>"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="notifications-empty-state text-center mx-auto">
                                    <div class="notifications-empty-icon-wrap"><i class="bi bi-bell-slash notifications-empty-icon"></i></div>
                                    <h6 class="notifications-empty-title mb-1">No notifications yet</h6>
                                    <p class="notifications-empty-text mb-0">You will see notifications here when there are new activities.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card notifications-list-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Notifications List</h5>
                            <span class="badge bg-secondary"><?php echo e($notifications->total()); ?></span>
                        </div>
                        <div class="card-body notifications-list-wrap">
                            <?php if($notifications->count() > 0): ?>
                                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $notificationKey = $notification->public_id ?: $notification->id; ?>
                                    <button type="button"
                                            class="notification-list-item <?php echo e($loop->first ? 'active' : ''); ?> <?php echo e($notification->read_at ? 'is-read' : 'is-unread'); ?>"
                                            data-id="<?php echo e($notificationKey); ?>"
                                            data-title="<?php echo e(e($notification->title)); ?>"
                                            data-message="<?php echo e(e($notification->message)); ?>"
                                            data-priority="<?php echo e($notification->priority); ?>"
                                            data-created="<?php echo e($notification->created_at->diffForHumans()); ?>"
                                            data-expires="<?php echo e($notification->expires_at ? $notification->expires_at->diffForHumans() : ''); ?>"
                                            data-action-url="<?php echo e($notification->action_url ?? ''); ?>"
                                            data-read="<?php echo e($notification->read_at ? '1' : '0'); ?>">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="notification-list-title"><?php echo e(Str::limit($notification->title, 46)); ?></div>
                                                <div class="notification-list-text"><?php echo e(Str::limit($notification->message, 70)); ?></div>
                                            </div>
                                            <?php if(!$notification->read_at): ?>
                                                <span class="notification-dot"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="notification-list-time"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="notifications-empty-state text-center mx-auto">
                                    <div class="notifications-empty-icon-wrap"><i class="bi bi-bell-slash notifications-empty-icon"></i></div>
                                    <h6 class="notifications-empty-title mb-1">No notifications</h6>
                                    <p class="notifications-empty-text mb-0">Nothing to show right now.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-center">
                                <?php echo e($notifications->links()); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/seller-notifications.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\seller\notifications\index.blade.php ENDPATH**/ ?>