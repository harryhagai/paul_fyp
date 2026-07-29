


<?php $__env->startSection('title', 'Product Media Management - ' . $product->name . ' - KidsStore Seller'); ?>

<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/seller-product-media.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $uploadMaxBytes = 50 * 1024 * 1024; // 50MB logical media limit (chunked upload)
?>

<div class="container-fluid mt-2 product-media-page"
     data-product-id="<?php echo e($product->public_id); ?>"
     data-upload-url="<?php echo e(route('seller.products.media.upload', ['productId' => $product->public_id])); ?>"
     data-primary-url-template="<?php echo e(route('seller.products.media.primary', ['productId' => $product->public_id, 'mediaId' => '__MEDIA_ID__'])); ?>"
     data-delete-url-template="<?php echo e(route('seller.products.media.delete', ['productId' => $product->public_id, 'mediaId' => '__MEDIA_ID__'])); ?>"
     data-upload-max-bytes="<?php echo e($uploadMaxBytes); ?>">
    <div class="toast-container position-fixed top-0 end-0 p-3" id="mediaToastContainer" style="z-index: 1080;"></div>
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 page-title">
                        <i class="bi bi-images me-3"></i>Product Media Management
                    </h1>
                    <p class="page-subtitle mb-0">Manage images and videos for: <strong><?php echo e($product->name); ?></strong></p>
                </div>
                <button type="button"
                        class="btn btn-outline-secondary themed-outline-btn"
                        data-spin-on-click
                        data-loading-text="Returning..."
                        onclick="window.location.href='<?php echo e(route('seller.products')); ?>'">
                    <i class="bi bi-arrow-left me-2"></i>Back to Products
                </button>
            </div>

            <!-- Upload Zone -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Media
                    </h5>
                </div>
                <div class="card-body">
                    <div class="media-upload-zone" id="mediaUploadZone">
                        <i class="bi bi-cloud-upload-fill mb-3" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5>Drag & Drop Files Here</h5>
                        <p class="text-muted">Or click to browse your files</p>
                        <small class="text-muted">
                            Supported formats: JPG, PNG, GIF (Images) • MP4, AVI (Videos)<br>
                            Max file size: 50MB per file (uploaded in chunks)
                        </small>
                        <input type="file" id="mediaInput" class="d-none" multiple accept="image/*,video/*">
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-primary" id="browseBtn">
                            <i class="bi bi-folder2-open me-2"></i>Browse Files
                        </button>
                        <button class="btn btn-outline-info" id="uploadSelectedBtn" style="display: none;">
                            <i class="bi bi-upload me-2"></i>Upload Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                    <div class="mt-3 d-none" id="uploadProgressWrap">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="uploadProgressLabel">Uploading...</span>
                            <span id="uploadProgressPercent">0%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Gallery -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Media Gallery (<?php echo e($product->media->count()); ?>)
                    </h5>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary active" id="gridView">Grid</button>
                        <button class="btn btn-sm btn-outline-primary" id="listView">List</button>
                    </div>
                </div>
                <div class="card-body" id="mediaGallery">
                    <?php if($product->media->isEmpty()): ?>
                        <div class="product-media-empty-state text-center mx-auto">
                            <div class="product-media-empty-icon-wrap">
                                <i class="bi bi-images product-media-empty-icon"></i>
                            </div>
                            <h6 class="product-media-empty-title mb-1">No media found</h6>
                            <p class="product-media-empty-text mb-0">Start by uploading images and videos for this product.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3 media-grid">
                            <?php $__currentLoopData = $product->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-3 col-sm-6">
                                    <div class="media-item" data-media-id="<?php echo e($media->id); ?>">
                                        <?php if($media->type === 'image'): ?>
                                            <img src="<?php echo e(asset('storage/' . $media->file_path)); ?>"
                                                 alt="Product Media" class="media-preview"
                                                 onclick="openMediaModal('<?php echo e(asset('storage/' . $media->file_path)); ?>', '<?php echo e($media->type); ?>')">
                                        <?php else: ?>
                                            <div class="bg-dark d-flex align-items-center justify-content-center media-preview"
                                                 onclick="openMediaModal('<?php echo e(asset('storage/' . $media->file_path)); ?>', '<?php echo e($media->type); ?>')">
                                                <i class="bi bi-play-circle-fill" style="font-size: 3rem; color: white;"></i>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($media->is_primary): ?>
                                            <div class="primary-badge">Primary</div>
                                        <?php endif; ?>

                                        <div class="media-overlay">
                                            <?php if(!$media->is_primary): ?>
                                                <button class="btn btn-light btn-overlay me-1" onclick="setAsPrimary(<?php echo e($media->id); ?>)">
                                                    <i class="bi bi-star"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-danger btn-overlay" onclick="deleteMedia(<?php echo e($media->id); ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="media-info">
                                        <div class="media-type-badge"><?php echo e(ucfirst($media->type)); ?></div>
                                        <small class="text-muted"><?php echo e($media->created_at->format('M j, Y')); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div id="mediaModalContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/seller-product-media.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\seller\product-media.blade.php ENDPATH**/ ?>