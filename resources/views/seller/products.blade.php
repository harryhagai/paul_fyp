@extends('layouts.dashboard')

@section('title', 'Products Management - KidsStore Seller')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/seller-products.css') }}">
@endsection

@section('content')

    <div class="container-fluid mt-2 seller-products-page" data-index-url="{{ route('seller.products') }}"
        data-store-url="{{ route('seller.products.store') }}"
        data-show-url-template="{{ route('seller.products.show', ['id' => '__ID__']) }}"
        data-update-url-template="{{ route('seller.products.update', ['id' => '__ID__']) }}"
        data-destroy-url-template="{{ route('seller.products.destroy', ['id' => '__ID__']) }}"
        data-toggle-advertised-url-template="{{ route('seller.products.toggleAdvertised', ['id' => '__ID__']) }}"
        data-csrf="{{ csrf_token() }}">
        <div class="row">
            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1 products-page-title">
                            <i class="bi bi-box-seam me-3"></i>Products Management
                        </h1>
                        <p class="products-page-subtitle mb-0">Manage your inventory, pricing, and product visibility in one
                            place.</p>
                    </div>
                    <button class="btn btn-outline-primary themed-outline-btn" data-bs-toggle="modal"
                        data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Product
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4 search-filter">
                    <div class="card-body">
                        <form id="searchForm" method="GET" action="{{ route('seller.products') }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchInput" name="search" class="form-control"
                                            placeholder="Search products..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="category_id" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-primary themed-outline-btn w-100"
                                        id="searchBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" id="searchSpinner"
                                            role="status" aria-hidden="true"></span>
                                        <i class="bi bi-search me-2" id="searchIcon"></i>Search
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('seller.products') }}" class="btn btn-outline-secondary w-100"
                                        id="resetFiltersBtn">
                                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Products List</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary themed-outline-btn" id="refreshBtn">
                                <i class="bi bi-arrow-repeat me-1"></i>Refresh
                            </button>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoRefresh">
                                <label class="form-check-label" for="autoRefresh">Auto Refresh</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>S/No.</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Robot Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    @include('seller.partials.product_rows', ['products' => $products])
                                </tbody>
                            </table>
                        </div>

                        <div id="lazyLoader" class="text-center py-3 d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading more
                            products...
                        </div>
                        <div id="scrollSentinel" class="lazy-sentinel" aria-hidden="true"></div>

                        <!-- Pagination fallback -->
                        <div class="pagination-wrapper d-none" id="productsPagination" style="margin-top: 2px;">
                            <div class="d-flex justify-content-center">
                                {{ $products->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="addProductModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3 product-form-grid">
                            <div class="col-lg-6 product-form-column">
                                <!-- Left Column -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="product-form-section-title">
                                            <i class="bi bi-box-seam"></i>
                                            <span>Product &amp; Inventory</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="addName" class="form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="addName" name="name"
                                            required>
                                    </div>
                                    <div class="col-md-7">
                                        <label for="addCategory" class="form-label">Category <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="addCategory" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="addStock" class="form-label">Stock <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="addStock" name="stock"
                                            required>
                                    </div>
                                    <div class="col-12">
                                        <div class="robot-location-field">
                                            <div class="robot-location-heading">
                                                <label for="addRobotLocation" class="form-label mb-0">
                                                    <i class="bi bi-robot"></i>Robot Storage Location
                                                </label>
                                                <span class="robot-location-range">Allowed: 1–{{ max(config('robot.locations', range(1, 5))) }}</span>
                                            </div>
                                            <div class="input-group robot-location-input">
                                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                <input type="number" class="form-control" id="addRobotLocation"
                                                    name="robot_location" min="1"
                                                    max="{{ max(config('robot.locations', range(1, 5))) }}" step="1"
                                                    placeholder="Example: 3">
                                            </div>
                                            <small class="robot-location-help">Leave empty only when this product will not be handled by the robot.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="addNewPrice" class="form-label">New Price <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Tsh</span>
                                            <input type="number" step="0.01" class="form-control" id="addNewPrice"
                                                name="new_price" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="addOldPrice" class="form-label">Old Price (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Tsh</span>
                                            <input type="number" step="0.01" class="form-control" id="addOldPrice"
                                                name="old_price">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="addThumbnail" class="form-label">Thumbnail</label>
                                        <input type="file" class="form-control" id="addThumbnail" name="thumbnail"
                                            accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 product-form-column">
                                <!-- Right Column -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="product-form-section-title">
                                            <i class="bi bi-card-text"></i>
                                            <span>Product Information</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="addDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="addDescription" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="addSpecifications" class="form-label">Specifications</label>
                                        <textarea class="form-control" id="addSpecifications" name="specifications" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary themed-outline-btn"
                            data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary themed-outline-btn db-primary-btn"
                            id="saveProductBtn">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                style="display: none;"></span>
                            <i class="bi bi-check2 me-2"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="editProductModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm" enctype="multipart/form-data">
                    <input type="hidden" id="editProductId" name="product_id">
                    <div class="modal-body">
                        <div class="row g-3 product-form-grid">
                            <div class="col-lg-6 product-form-column">
                                <!-- Left Column -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="product-form-section-title">
                                            <i class="bi bi-box-seam"></i>
                                            <span>Product &amp; Inventory</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="editName" class="form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editName" name="name"
                                            required>
                                    </div>
                                    <div class="col-md-7">
                                        <label for="editCategory" class="form-label">Category <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="editCategory" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="editStock" class="form-label">Stock <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="editStock" name="stock"
                                            required>
                                    </div>
                                    <div class="col-12">
                                        <div class="robot-location-field">
                                            <div class="robot-location-heading">
                                                <label for="editRobotLocation" class="form-label mb-0">
                                                    <i class="bi bi-robot"></i>Robot Storage Location
                                                </label>
                                                <span class="robot-location-range">Allowed: 1–{{ max(config('robot.locations', range(1, 5))) }}</span>
                                            </div>
                                            <div class="input-group robot-location-input">
                                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                <input type="number" class="form-control" id="editRobotLocation"
                                                    name="robot_location" min="1"
                                                    max="{{ max(config('robot.locations', range(1, 5))) }}" step="1"
                                                    placeholder="Example: 3">
                                            </div>
                                            <small class="robot-location-help">Leave empty only when this product will not be handled by the robot.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editNewPrice" class="form-label">New Price <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Tsh</span>
                                            <input type="number" step="0.01" class="form-control" id="editNewPrice"
                                                name="new_price" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editOldPrice" class="form-label">Old Price (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Tsh</span>
                                            <input type="number" step="0.01" class="form-control" id="editOldPrice"
                                                name="old_price">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="editThumbnail" class="form-label">Thumbnail</label>
                                        <input type="file" class="form-control" id="editThumbnail" name="thumbnail"
                                            accept="image/*">
                                        <div id="currentThumbnailPreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 product-form-column">
                                <!-- Right Column -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="product-form-section-title">
                                            <i class="bi bi-card-text"></i>
                                            <span>Product Information</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="editDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="editDescription" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="editSpecifications" class="form-label">Specifications</label>
                                        <textarea class="form-control" id="editSpecifications" name="specifications" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary themed-outline-btn"
                            data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary themed-outline-btn" id="updateProductBtn">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                style="display: none;"></span>
                            <i class="bi bi-check2 me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Product Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="viewProductModalLabel">
                        <i class="bi bi-eye me-2"></i>Product Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label">Product</label>
                            <div class="p-3 border rounded-3 bg-light-subtle view-product-block">
                                <div class="view-product-image-wrap mb-3">
                                    <img id="viewThumbnail" src="" alt="Product Image"
                                        class="view-product-image border">
                                </div>
                                <div class="view-product-meta">
                                    <h5 id="viewName" class="mb-2"></h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge view-chip view-chip-muted" id="viewCategory"></span>
                                        <span class="badge view-chip view-chip-primary-soft" id="viewStock"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Price</label>
                                <div class="form-control bg-light" id="viewPrice"></div>
                                <small class="text-decoration-line-through text-muted d-block mt-1" id="viewOldPrice"
                                    style="display: none;"></small>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Status</label>
                                <div class="d-flex gap-2">
                                    <span class="badge view-chip view-chip-primary" id="viewStatus"></span>
                                    <span class="badge view-chip view-chip-primary-soft" id="viewDiscount"></span>
                                    <span class="badge view-chip view-chip-muted" id="viewRobotLocation"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea id="viewDescription" class="form-control" rows="3" readonly></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Specifications</label>
                                <textarea id="viewSpecifications" class="form-control" rows="3" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary themed-outline-btn" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/seller-products.js') }}"></script>
@endpush
