@extends('layouts.app')

@section('title', 'Categories - KidsStore365')

@section('css')
    <link href="{{ asset('css/categories.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="categories-page py-4 py-md-5">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h1 class="categories-title mb-2">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Shop by Category
                </h1>
                <p class="categories-subtitle mb-0">
                    Discover collections for your little ones.
                </p>
            </div>

            <div class="categories-search-wrap mb-4 mb-md-5">
                <form method="GET" action="{{ route('categories') }}" class="row g-2 align-items-center"
                    id="categories-search-form">
                    <div class="col">
                        <input type="text" name="search" class="form-control categories-search-input"
                            placeholder="Search categories..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn categories-search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div id="categories-results">
                <div class="row g-3 g-md-4">
                    @forelse($categories as $category)
                        @php
                            $icons = [
                                'baby-clothes' => 'bi-t-shirt',
                                'kids-toys' => 'bi-rocket-takeoff',
                                'gifts-hampers' => 'bi-gift',
                            ];
                            $icon = $icons[$category->slug] ?? 'bi-circle';
                        @endphp

                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('category.show', $category->slug) }}"
                                class="category-tile card h-100 text-decoration-none">
                                <div class="card-body d-flex flex-column">
                                    <div class="category-icon mb-2">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <h2 class="category-name mb-2">{{ $category->name }}</h2>
                                    @if ($category->description)
                                        <p class="category-description mb-3">{{ Str::limit($category->description, 70) }}</p>
                                    @endif
                                    <div class="category-meta mt-auto">
                                        <i class="bi bi-box-seam me-1"></i>
                                        {{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="categories-empty text-center p-4 p-md-5">
                                <i class="bi bi-search mb-3 d-inline-block"></i>
                                <h3 class="h5 mb-2">No categories found</h3>
                                <p class="mb-3">Try a different search keyword.</p>
                                <div class="row g-2 justify-content-start">
                                    <div class="col-6 col-sm-auto">
                                        <a href="{{ route('shop') }}" class="btn btn-primary w-100">Browse Products</a>
                                    </div>
                                    <div class="col-12 col-sm-auto">
                                        <a href="{{ route('categories') }}" class="btn btn-outline-primary w-100">Clear Search</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/categories.js') }}"></script>
@endsection
