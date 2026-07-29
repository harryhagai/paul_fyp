@extends('layouts.app')

@section('title', $category->name . ' - KidsStore365')

@section('css')
    <link href="{{ asset('css/shop.css') }}" rel="stylesheet">
    <link href="{{ asset('css/category.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="category-page py-4 py-md-5">
    <div class="container">
        <div class="category-hero card border-0 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <h1 class="category-title mb-1">{{ $category->name }}</h1>
                        @if($category->description)
                            <p class="category-subtitle mb-0">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="category-count badge rounded-pill">{{ $products->total() }} products</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="category-controls card border-0 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('category.show', $category->slug) }}" id="category-search-form" class="row g-2">
                    <div class="col-12 col-md">
                        <input type="text" name="search" class="form-control category-search-input" placeholder="Search in {{ $category->name }}..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        @php
                            $selectedSort = request('sort_by') && request('sort_order') ? request('sort_by') . '-' . request('sort_order') : '';
                        @endphp
                        <select name="sort" class="form-select" id="sort-select">
                            <option value="">Sort by</option>
                            <option value="name-asc" {{ $selectedSort === 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name-desc" {{ $selectedSort === 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="new_price-asc" {{ $selectedSort === 'new_price-asc' ? 'selected' : '' }}>Price (Low to High)</option>
                            <option value="new_price-desc" {{ $selectedSort === 'new_price-desc' ? 'selected' : '' }}>Price (High to Low)</option>
                            <option value="created_at-desc" {{ $selectedSort === 'created_at-desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="rate-desc" {{ $selectedSort === 'rate-desc' ? 'selected' : '' }}>Highest Rated</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-auto d-grid">
                        <button type="submit" class="btn category-search-btn"><i class="bi bi-search"></i></button>
                    </div>
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    <input type="hidden" name="in_stock" value="{{ request('in_stock') }}">
                    <input type="hidden" name="on_sale" value="{{ request('on_sale') }}">
                    <input type="hidden" name="rating" value="{{ request('rating') }}">
                </form>

                <div class="d-flex flex-wrap gap-2 mt-3" id="filter-actions">
                    @php
                        $queryParams = request()->query();
                        $isInStock = request('in_stock') == '1';
                        $isOnSale = request('on_sale') == '1';
                        $isHighRating = request('rating') == '4';
                    @endphp

                    <a href="{{ route('category.show', $category->slug, array_merge($queryParams, ['in_stock' => $isInStock ? null : '1', 'page' => null])) }}" class="btn btn-sm filter-chip {{ $isInStock ? 'active' : '' }}" data-filter-link>
                        <i class="bi bi-check-circle me-1"></i>In Stock
                    </a>
                    <a href="{{ route('category.show', $category->slug, array_merge($queryParams, ['on_sale' => $isOnSale ? null : '1', 'page' => null])) }}" class="btn btn-sm filter-chip {{ $isOnSale ? 'active' : '' }}" data-filter-link>
                        <i class="bi bi-percent me-1"></i>On Sale
                    </a>
                    <a href="{{ route('category.show', $category->slug, array_merge($queryParams, ['rating' => $isHighRating ? null : '4', 'page' => null])) }}" class="btn btn-sm filter-chip {{ $isHighRating ? 'active' : '' }}" data-filter-link>
                        <i class="bi bi-star-fill me-1"></i>4+ Stars
                    </a>
                    @if($isInStock || $isOnSale || $isHighRating || request('search') || request('sort_by') || request('sort_order'))
                        <a href="{{ route('category.show', $category->slug) }}" class="btn btn-sm filter-chip clear" data-filter-link>
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="other-categories mb-4">
            <div class="d-flex flex-wrap gap-2">
                @foreach($categories->where('id', '!=', $category->id) as $cat)
                    <a href="{{ route('category.show', $cat->slug) }}" class="btn btn-sm btn-light border">
                        {{ $cat->name }}
                        <span class="badge text-bg-secondary ms-1">{{ $cat->products_count ?? $cat->products->count() }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div id="category-results">
            <div class="products-grid" id="productsContainer">
                @forelse($products as $product)
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
                @empty
                    <div class="category-empty-wrap">
                    <div class="category-empty text-center p-4 p-md-5">
                        <i class="bi bi-search d-inline-block mb-2"></i>
                        <h3 class="h5">No products found</h3>
                        <p class="mb-3">Try adjusting search or filters.</p>
                        <a href="{{ route('category.show', $category->slug) }}" class="btn category-reset-btn">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 category-pagination-wrap">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/category.js') }}"></script>
@endsection
