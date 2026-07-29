


<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin-settings.css')); ?>" rel="stylesheet">
<link href="<?php echo e(asset('css/admin-seller-permissions.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-shield-lock me-3"></i>Seller Edit Permissions</h1>
    </div>

    <?php
        $allowedGroups = ['header', 'footer'];
        $groups = collect($components)->keys()->values()->filter(fn($g) => in_array($g, $allowedGroups))->values();
    ?>

    <div class="card panel-card">
        <div class="panel-card-head p-0">
            <ul class="nav nav-tabs settings-tabs" id="permissionGroupsTab" role="tablist">
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo e($loop->first ? 'active' : ''); ?>" id="tab-<?php echo e($group); ?>" data-bs-toggle="tab" data-bs-target="#pane-<?php echo e($group); ?>" type="button" role="tab">
                            <?php echo e(ucfirst($group)); ?>

                        </button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>

        <div class="panel-card-body">
            <form method="POST" action="<?php echo e(route('admin.settings.seller-permissions.bulk-update')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="tab-content" id="permissionGroupsContent">
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $groupComponents = $components[$group] ?? []; ?>
                        <div class="tab-pane fade <?php echo e($loop->first ? 'show active' : ''); ?>" id="pane-<?php echo e($group); ?>" role="tabpanel">
                            <p class="section-note">Allow or deny seller editing for each full <?php echo e($group); ?> component.</p>
                            <div class="row g-3">
                                <?php $__currentLoopData = $groupComponents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-lg-6">
                                        <div class="permission-component-card">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="permission-title"><?php echo e($component['label']); ?></div>
                                                    <div class="permission-desc"><?php echo e($component['description']); ?></div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input
                                                        class="form-check-input permission-toggle"
                                                        type="checkbox"
                                                        name="components[<?php echo e($group); ?>][<?php echo e($component['id']); ?>]"
                                                        id="component_<?php echo e($group); ?>_<?php echo e($component['id']); ?>"
                                                        <?php echo e(!empty($component['enabled']) ? 'checked' : ''); ?>

                                                    >
                                                    <label class="form-check-label" for="component_<?php echo e($group); ?>_<?php echo e($component['id']); ?>">Can Edit</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/admin/settings/seller-permissions.blade.php ENDPATH**/ ?>