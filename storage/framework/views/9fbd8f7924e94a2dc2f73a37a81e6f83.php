


<?php $__env->startSection('title', 'Categories Management - KidsStore Seller'); ?>

<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/seller-categories.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-2 seller-categories-page"
     data-next-page-url="<?php echo e($categories->nextPageUrl()); ?>"
     data-store-url="<?php echo e(route('seller.categories.store')); ?>"
     data-show-url-template="<?php echo e(route('seller.categories.show', ['id' => '__ID__'])); ?>"
     data-update-url-template="<?php echo e(route('seller.categories.update', ['id' => '__ID__'])); ?>"
     data-destroy-url-template="<?php echo e(route('seller.categories.destroy', ['id' => '__ID__'])); ?>"
     data-csrf="<?php echo e(csrf_token()); ?>">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 categories-page-title">
                        <i class="bi bi-tags me-3"></i>Categories Management
                    </h1>
                    <p class="categories-page-subtitle mb-0">Create, update, and organize product categories quickly.</p>
                </div>
                <button class="btn btn-outline-primary themed-outline-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Category
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <!-- Categories Summary Scroll -->
            <div class="category-summary-scroll mb-4" id="categorySummaryList">
                <?php if($categories->count()): ?>
                    <?php echo $__env->make('seller.partials.category_summary_items', ['categories' => $categories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                <div class="w-100">
                    <div class="category-empty-state category-empty-state-lg mx-auto text-center">
                        <div class="category-empty-icon-wrap">
                            <i class="bi bi-tags category-empty-icon"></i>
                        </div>
                        <h6 class="category-empty-title mb-1">No categories found</h6>
                        <p class="category-empty-text mb-3">Create your first category to start organizing products.</p>
                        <button class="btn btn-outline-primary themed-outline-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-circle me-2"></i>Create your first category
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Categories Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 category-table-head">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-list-check me-2"></i>All Categories
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="category-name-col">Category Name</th>
                                    <th class="category-description-col">Description</th>
                                    <th class="fit-content-col">Products Count</th>
                                    <th class="fit-content-col">Created</th>
                                    <th class="fit-content-col text-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <?php if($categories->count()): ?>
                                    <?php echo $__env->make('seller.partials.category_rows', ['categories' => $categories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="category-empty-state mx-auto text-center">
                                            <div class="category-empty-icon-wrap">
                                                <i class="bi bi-tags category-empty-icon"></i>
                                            </div>
                                            <h6 class="category-empty-title mb-1">No categories found</h6>
                                            <p class="category-empty-text mb-0">Try adding a category to populate this table.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="lazyLoader" class="text-center py-3 d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading more
                        categories...
                    </div>
                    <div id="scrollSentinel" class="lazy-sentinel" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="viewCategoryModalLabel">
                        <i class="bi bi-eye me-2"></i>Category Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="view_name" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="view_description" rows="3" readonly></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Products Count</label>
                            <input type="text" class="form-control" id="view_products_count" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Created</label>
                            <input type="text" class="form-control" id="view_created_at" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="view_slug" readonly>
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

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
            <form id="createCategoryForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="createCategoryModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="create_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" maxlength="191" required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                        </div>
                        <div class="col-12">
                            <label for="create_description" class="form-label">Description</label>
                            <textarea class="form-control" id="create_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary themed-outline-btn db-primary-btn" id="saveCategoryBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="bi bi-check2 me-2"></i>Save Category
                    </button>
                </div>
            </form>
            </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
            <form id="editCategoryForm">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="editCategoryModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" maxlength="191" required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                        </div>
                        <div class="col-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary themed-outline-btn db-primary-btn" id="updateCategoryBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="bi bi-check2 me-2"></i>Update Category
                    </button>
                </div>
            </form>
            </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/seller-categories.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views/seller/categories.blade.php ENDPATH**/ ?>