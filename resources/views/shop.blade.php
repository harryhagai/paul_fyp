@extends('layouts.app')

@section('title', 'Shop - KidsStore')
@section('hideFooter', 'true')

@section('css')
<link rel="stylesheet" href="{{ asset('css/shop.css') }}">
@endsection

@section('content')
<main class="shop-container">
<!-- Search and Categories in Header -->
<div class="shop-header-sticky">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Search Bar Section -->
                <div class="search-section mb-3">
                    @php
                        $selectedSort = request('sort_by') && request('sort_order') ? request('sort_by') . '-' . request('sort_order') : '';
                    @endphp
                    <form method="GET" action="{{ route('shop') }}" id="shop-search-form">
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap flex-md-nowrap search-sort-row">
                            <div class="search-bar position-relative search-bar-compact">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
                                <button type="submit" class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <select class="form-select form-select-sm shop-sort-select" id="shop-sort-select" aria-label="Sort products">
                                <option value="">Sort by</option>
                                <option value="created_at-desc" {{ $selectedSort === 'created_at-desc' ? 'selected' : '' }}>Newest</option>
                                <option value="name-asc" {{ $selectedSort === 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="name-desc" {{ $selectedSort === 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                                <option value="new_price-asc" {{ $selectedSort === 'new_price-asc' ? 'selected' : '' }}>Price (Low to High)</option>
                                <option value="new_price-desc" {{ $selectedSort === 'new_price-desc' ? 'selected' : '' }}>Price (High to Low)</option>
                                <option value="rate-desc" {{ $selectedSort === 'rate-desc' ? 'selected' : '' }}>Highest Rated</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Categories Section -->
                <div class="categories-header-section">
                    <div class="categories-grid d-flex gap-2 justify-content-start" id="shop-categories-grid">
                        @php
                            $allCategoriesQuery = request()->query();
                            unset($allCategoriesQuery['category']);
                        @endphp
                        <a href="{{ route('shop') }}?{{ http_build_query($allCategoriesQuery) }}" class="category-pill btn btn-sm {{ !request('category') ? 'category-pill-selected' : 'category-pill-default' }}">
                            <i class="bi bi-grid-fill me-1"></i>All Categories
                        </a>
                        @foreach($categories as $category)
                            @php
                                $categoryQuery = request()->query();
                                $categoryQuery['category'] = $category->id;
                            @endphp
                        <a href="{{ route('shop') }}?{{ http_build_query($categoryQuery) }}" class="category-pill btn btn-sm {{ request('category') == $category->id ? 'category-pill-selected' : 'category-pill-default' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controls Bar -->
<div class="controls-bar" style="display: none;">
</div>

    <!-- Products Grid -->
    <!-- Search Loading Spinner -->
    <div id="search-loading-spinner" class="text-center py-4" style="display: none;">
        <div class="loading-spinner"></div>
        <p class="text-muted mt-2 small">Searching products...</p>
    </div>

    <div class="products-grid" id="productsContainer">
        @if($products->count() > 0)
        @foreach($products as $product)
        <article class="product-card">
            <div class="product-image">
                <a href="{{ route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug]) }}" class="text-decoration-none">
                    <img src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png') }}" alt="{{ $product->name }}" loading="lazy">
                </a>
                <div class="product-badges">
                    @if($product->created_at->diffInDays(now()) <= 7)
                        <span class="product-badge badge-new">New</span>
                    @endif
                </div>
            </div>

            <div class="product-info">
                <h3 class="product-title">
                    <a href="{{ route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug]) }}" class="text-decoration-none">
                        {{ $product->name }}
                    </a>
                </h3>

                @if($product->description?->description)
                <p class="product-description">
                    {{ Str::limit($product->description->description, 60) }}
                </p>
                @endif

                <div class="product-prices">
        <span class="product-price">Tsh {{ number_format((float) $product->new_price, 0) }}</span>
                    @if($product->old_price && $product->old_price > $product->new_price)
                        <span class="product-old-price">Tsh {{ number_format((float) $product->old_price, 0) }}</span>
                    @endif
                </div>

                <div class="product-rating">
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($product->rate > 0)
                                <i class="bi {{ $i <= round($product->rate) ? 'bi-star-fill' : 'bi-star' }} star"></i>
                            @else
                                <i class="bi bi-star star text-secondary"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="rating-count">({{ number_format((float) $product->rate, 1) }})</span>
                    <span class="stock-status {{ $product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out') }}">
                        @if($product->stock > 10)
                            In Stock: {{ $product->stock }}
                        @elseif($product->stock > 0)
                            In Stock: {{ $product->stock }}
                        @else
                            Out of Stock
                        @endif
                    </span>
                </div>

                <div class="product-meta">
                    <span class="category">
                        <i class="bi bi-tag-fill"></i> {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
            </div>
        </article>
        @endforeach
        @else
        <div class="no-products-found">
            <div class="shop-empty text-center p-4 p-md-5">
                <i class="bi bi-search d-inline-block mb-2"></i>
                <h3 class="h5 mb-2">No products found</h3>
                <p class="mb-3">Try adjusting your search criteria or browse different categories</p>
                <a href="{{ route('shop') }}" class="btn shop-empty-btn">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        @endif
    </div>


    <!-- Pagination -->
    <div id="shop-pagination" style="display:none;">
        {{ $products->appends(request()->query())->links() }}
    </div>
    <div id="shop-infinite-loader" class="text-center py-3" style="display:none;">
        <span class="loading-spinner"></span>
    </div>
    <div id="shop-infinite-end" class="text-center py-2 text-muted small" style="display:none;">
        No more products
    </div>
    <div id="shop-scroll-sentinel" style="height: 1px;"></div>

</main>

@section('scripts')
<script src="{{ asset('js/shop.js') }}"></script>
@endsection

@endsection
