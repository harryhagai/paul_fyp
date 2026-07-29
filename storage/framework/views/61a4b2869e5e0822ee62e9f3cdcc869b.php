


<?php $__env->startSection('title', 'Customer Management - KidsStore Seller'); ?>
<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/seller-customers.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $customersIndexRoute = $isAdmin ? route('admin.customers') : route('seller.customers');
        $customersStoreRoute = $isAdmin ? route('admin.customers.store') : route('seller.customers.store');
        $customersShowRoute = $isAdmin
            ? route('admin.customers.show', ['id' => ':id'])
            : route('seller.customers.show', ['id' => ':id']);
        $customersUpdateRoute = $isAdmin
            ? route('admin.customers.update', ['id' => ':id'])
            : route('seller.customers.update', ['id' => ':id']);
        $customersDeleteRoute = $isAdmin
            ? route('admin.customers.destroy', ['id' => ':id'])
            : route('seller.customers.destroy', ['id' => ':id']);
    ?>
    <div class="container-fluid mt-2" id="sellerCustomersPage" data-store-url="<?php echo e($customersStoreRoute); ?>" data-show-url="<?php echo e($customersShowRoute); ?>" data-update-url="<?php echo e($customersUpdateRoute); ?>" data-delete-url="<?php echo e($customersDeleteRoute); ?>" data-csrf-token="<?php echo e(csrf_token()); ?>" data-avatar-base-url="<?php echo e(asset('storage/profile_photos')); ?>">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-people me-3"></i><?php echo e($isAdmin ? 'User Management' : 'Customer & Seller Management'); ?>

                    </h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-plus-circle me-2"></i><?php echo e($isAdmin ? 'Add New User' : 'Add New User'); ?>

                    </button>
                </div>

                <!-- Search -->
                <div class="card mb-4 search-filter">
                    <div class="card-body">
                        <form id="searchForm" method="GET" action="<?php echo e($customersIndexRoute); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" name="search" class="form-control"
                                            placeholder="Search by name or email..." value="<?php echo e(request('search')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-primary w-100" id="searchBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" id="searchSpinner"
                                            role="status" aria-hidden="true"></span>
                                        <i class="bi bi-search me-2" id="searchIcon"></i>Search
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo e($customersIndexRoute); ?>" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Customers List</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="refreshBtn">
                                <i class="bi bi-arrow-repeat me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>S/No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($customer->profile_photo): ?>
                                                        <img src="<?php echo e(asset('storage/profile_photos/' . $customer->profile_photo)); ?>"
                                                            alt="Profile Picture" class="rounded-circle me-3"
                                                            style="width: 35px; height: 35px; object-fit: cover;" />
                                                    <?php else: ?>
                                                        <div class="avatar-circle me-3"
                                                            style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--teal-primary, #0d9488) 0%, var(--teal-secondary, #0f766e) 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                                                            <?php echo e(strtoupper(substr($customer->name, 0, 1))); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo e($customer->name); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo e($customer->email); ?></td>
                                            <td><span
                                                    class="badge bg-secondary text-uppercase"><?php echo e($customer->role); ?></span>
                                            </td>
                                            <td><?php echo e($customer->created_at->format('M d, Y')); ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary action-btn view-btn"
                                                        data-bs-toggle="modal" data-bs-target="#viewCustomerModal"
                                                        data-customer-id="<?php echo e($customer->public_id); ?>">
                                                        <i class="bi bi-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary action-btn edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                                                        data-customer-id="<?php echo e($customer->public_id); ?>">
                                                        <i class="bi bi-pencil me-1"></i>Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger action-btn delete-btn"
                                                        data-customer-id="<?php echo e($customer->public_id); ?>">
                                                        <i class="bi bi-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-info-circle me-2"></i>No customers found.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-wrapper" style="margin-top: 2px;">
                            <div class="d-flex justify-content-center">
                                <?php echo e($customers->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal zoom-modal" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-white">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="addName" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addName" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addEmail" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="addEmail" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addRole" class="form-label">Role <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="addRole" name="role" required>
                                    <option value="customer">Customer</option>
                                    <option value="seller">Seller</option>
                                    <?php if($isAdmin): ?>
                                        <option value="admin">Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="addPassword" class="form-label">Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="addPassword" name="password"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="addPasswordConfirm" class="form-label">Confirm Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="addPasswordConfirm"
                                        name="password_confirmation" required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary" id="saveCustomerBtn">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                style="display: none;"></span>
                            <i class="bi bi-person-check me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal zoom-modal" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-white">
                    <h5 class="modal-title" id="editCustomerModalLabel">
                        <i class="bi bi-person-gear me-2"></i>Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCustomerForm">
                    <input type="hidden" id="editCustomerId" name="customer_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editName" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editName" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEmail" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editRole" class="form-label">Role <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editRole" name="role" required>
                                    <option value="customer">Customer</option>
                                    <option value="seller">Seller</option>
                                    <?php if($isAdmin): ?>
                                        <option value="admin">Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editPassword" class="form-label">New Password (leave empty to keep
                                    current)</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editPassword" name="password">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editPasswordConfirm" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editPasswordConfirm"
                                        name="password_confirmation">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary" id="updateCustomerBtn">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                style="display: none;"></span>
                            <i class="bi bi-pencil-square me-2"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal zoom-modal" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-white">
                    <h5 class="modal-title" id="viewCustomerModalLabel">
                        <i class="bi bi-person-vcard me-2"></i>Customer Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <div class="modal-body">
                <div class="view-user-shell">
                    <div class="view-user-profile">
                        <div id="viewAvatarContainer" class="mx-auto view-user-avatar">
                            <!-- Avatar will be populated by JavaScript -->
                        </div>
                        <h4 id="viewName" class="view-user-name mb-1"></h4>
                        <p class="view-user-subtitle mb-0">User Profile</p>
                    </div>

                    <div class="view-user-details">
                        <div class="view-user-item">
                            <small class="view-user-label">Email</small>
                            <div class="view-user-value" id="viewEmail"></div>
                        </div>
                        <div class="view-user-item">
                            <small class="view-user-label">Join Date</small>
                            <div class="view-user-value" id="viewJoinDate"></div>
                        </div>
                        <div class="view-user-item">
                            <small class="view-user-label">Role</small>
                            <div><span id="viewRole" class="badge view-role-badge text-uppercase">Customer</span></div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/seller-customers.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\seller\customers.blade.php ENDPATH**/ ?>