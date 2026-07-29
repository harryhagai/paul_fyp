@extends('layouts.app')

@section('title', $product->name . ' - KidsStore')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection


@section('content')
    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;">
        <div class="notification-content">
            <i class="bi bi-check-circle"></i>
            <span id="notification-message"></span>
        </div>
    </div>

    <div class="container py-3 py-md-4">
    <!-- Shop Menu Icon -->
    <div class="shop-menu-icon mb-3">
        <a href="{{ route('shop') }}" class="btn btn-outline-secondary btn-sm show-dot-btn" data-spin-link="1" title="Back to Shop">
            <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="button-text"><i class="bi bi-arrow-left"></i> Back to Shop</span>
        </a>
    </div>

    <div class="product-details">
        <div class="row g-4">
            <div class="col-lg-6">
                <!-- Product Gallery -->
                <div class="product-gallery card border-0 shadow-sm p-2 p-md-3">
                    <!-- Top row: Main image and left horizontal thumbnails -->
                    <div class="gallery-top-row">
                        <div class="gallery-sidebar">
                            <div class="gallery-thumbs-vertical">
                                @php
                                    $images = $product->media->where('type', 'image') ?: collect();
                                    $primaryImage = $images->where('is_primary', true)->first() ?: $images->first();
                                @endphp

                                @if ($primaryImage)
                                    @foreach ($images as $image)
                                        <div class="thumb-image {{ $loop->first ? 'active' : '' }}"
                                            data-image-src="{{ asset('storage/' . $image->file_path) }}"
                                            onclick="changeImage('{{ asset('storage/' . $image->file_path) }}', this)">
                                            <img src="{{ asset('storage/' . $image->file_path) }}"
                                                alt="{{ $product->name }}">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="main-image" id="mainImageFrame">
                            @php
                                $mainImage = $primaryImage
                                    ? asset('storage/' . $primaryImage->file_path)
                                    : asset('img/logo.png');
                            @endphp
                            @if ($primaryImage && $images->count() > 1)
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-prev"
                                    onclick="galleryPrev()"
                                    aria-label="Previous image">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-next"
                                    onclick="galleryNext()"
                                    aria-label="Next image">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @endif
                            <img id="mainImage" src="{{ $mainImage }}" alt="{{ $product->name }}">
                        </div>
                    </div>

                    @if ($primaryImage && $images->count() > 1)
                        <div class="mobile-gallery-dots">
                            @foreach ($images as $image)
                                <button
                                    type="button"
                                    class="gallery-dot {{ $loop->first ? 'active' : '' }}"
                                    data-image-src="{{ asset('storage/' . $image->file_path) }}"
                                    onclick="selectImageBySrc('{{ asset('storage/' . $image->file_path) }}')"
                                    aria-label="View image {{ $loop->iteration }}">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Bottom row: Horizontal thumbnails -->
                    @if ($primaryImage && $images->count() > 1)
                        <div class="horizontal-thumbs-container"> <!-- Add this wrapper -->
                            <div class="gallery-thumbs-horizontal">
                                @foreach ($images as $image)
                                    <div class="thumb-image-horizontal {{ $loop->first ? 'active' : '' }}"
                                        data-image-src="{{ asset('storage/' . $image->file_path) }}"
                                        onclick="changeImage('{{ asset('storage/' . $image->file_path) }}', this)">
                                        <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $product->name }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Product Info -->
                <div class="product-info card border-0 shadow-sm p-3 p-md-4">
                    <h1 class="product-main-title mb-2 fw-bold lh-sm">{{ $product->name }}
                        @if ($product->created_at > \Carbon\Carbon::now()->subDays(30))
                            <span class="badge bg-success align-middle">NEW</span>
                        @endif
                    </h1>

                    @php
                        $allFiveStars = $product->ratings->count() > 0
                            && $product->ratings->every(fn ($review) => (int) $review->rating === 5);
                        $averageRating = $allFiveStars
                            ? 5.0
                            : round((float) ($product->ratings->avg('rating') ?? 0), 1);
                        $totalReviews = $product->ratings->count();
                    @endphp
                    @if ($averageRating > 0)
                        <div class="product-rating">
                            <div class="stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $starValue = $averageRating - ($i - 1);
                                    @endphp
                                    <i class="bi {{ $starValue >= 1 ? 'bi-star-fill' : ($starValue >= 0.5 ? 'bi-star-half' : 'bi-star') }} star"></i>
                                @endfor
                            </div>
                            <span class="rating-text">{{ number_format($averageRating, 1) }} ({{ $totalReviews }} reviews)</span>
                        </div>
                    @endif

                    <div class="product-prices align-items-center">
                        <span class="current-price fw-bold">Tsh {{ number_format((float) $product->new_price, 0) }}</span>
                        @if ($product->old_price && $product->old_price > $product->new_price)
                            <span class="old-price text-danger">Tsh {{ number_format((float) $product->old_price, 0) }}</span>
                            <span class="badge bg-danger badge-sm">{{ $product->discount }}% OFF</span>
                        @endif
                    </div>

                    <div class="stock-qty-row">
                        <div
                            class="stock-info {{ $product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out') }}">
                            <i class="bi bi-circle-fill"></i>
                            @if ($product->stock > 10)
                                <span>In Stock ({{ $product->stock }} available)</span>
                            @elseif($product->stock > 0)
                                <span>Only {{ $product->stock }} left in stock</span>
                            @else
                                <span>Out of Stock</span>
                            @endif
                        </div>

                        <div class="quantity-selector">
                            <div class="quantity-input">
                                <button class="quantity-btn" onclick="changeQuantity(-1)" {{ $product->stock <= 0 ? 'disabled' : '' }}>-</button>
                                <input type="number" id="quantityInput" value="{{ $product->stock > 0 ? 1 : 0 }}" min="1"
                                    max="{{ $product->stock }}" readonly>
                                <button class="quantity-btn" onclick="changeQuantity(1)" {{ $product->stock <= 0 ? 'disabled' : '' }}>+</button>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons row g-2">
                        <div class="col-12">
                        <button class="btn-add-cart w-100 show-dot-btn"
                            {{ $product->stock <= 0 ? 'disabled' : '' }}
                            onclick="addToCart({{ $product->id }}, document.getElementById('quantityInput').value)">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-cart-plus"></i> {{ $product->stock <= 0 ? 'Out of Stock' : 'Add to Cart' }}</span>
                        </button>
                        </div>
                        @auth
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        @else
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        @endauth
                    </div>


                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-buttons-scrollable">
                <button class="tab-btn active" onclick="switchTab('description')">
                    <i class="bi bi-info-circle"></i>
                    <span>Description</span>
                </button>
                <button class="tab-btn" onclick="switchTab('specifications')">
                    <i class="bi bi-gear"></i>
                    <span>Specifications</span>
                </button>
                <button class="tab-btn" onclick="switchTab('reviews')">
                    <i class="bi bi-star"></i>
                    <span>Reviews</span>
                </button>
            </div>

            <div id="description" class="tab-content active">
                <h4>About this product</h4>
                <div class="prose">
                    {!! $product->description->description ?? '<p>No description available.</p>' !!}
                    {!! $product->description->details ?? '' !!}
                </div>
            </div>

            <div id="specifications" class="tab-content">
                <h4>Technical Specifications</h4>
                <div class="prose">
                    @if ($product->description && $product->description->specifications)
                        @php
                            $specs = trim($product->description->specifications);
                            $lines = array_filter(explode("\n", $specs), function ($line) {
                                return trim($line) !== '';
                            });
                            if (count($lines) > 0) {
                                echo '<ul class="spec-list">';
                                foreach ($lines as $line) {
                                    if (strpos($line, ':') !== false) {
                                        [$key, $value] = explode(':', $line, 2);
                                        echo '<li><strong>' .
                                            htmlspecialchars(trim($key)) .
                                            ':</strong> ' .
                                            htmlspecialchars(trim($value)) .
                                            '</li>';
                                    } else {
                                        echo '<li>' . htmlspecialchars(trim($line)) . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>No specifications available.</p>';
                            }
                        @endphp
                    @else
                        <p>No specifications available.</p>
                    @endif
                </div>
            </div>

            <div id="reviews" class="tab-content">
                <div class="reviews-heading">
                    <h4>Customer Reviews</h4>
                    @if ($totalReviews > 0)
                        <div class="reviews-heading-meta">
                            <span class="reviews-score">{{ number_format($averageRating, 1) }}</span>
                            <div class="reviews-stars-inline">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= round($averageRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            <span class="reviews-count">{{ $totalReviews }} reviews</span>
                        </div>
                    @endif
                </div>
                @if ($product->ratings->count() > 0)
                    <div class="reviews-carousel-container">
                        <div class="css-carousel" id="cssReviewsCarousel">
                            <div class="css-carousel-track" style="--total-slides: {{ $product->ratings->count() }}">
                                @foreach ($product->ratings as $index => $rating)
                                    <div class="css-carousel-slide">
                                        <div class="review-item">
                                            <div class="review-item-head">
                                                <div class="review-user">
                                                    <div class="review-avatar">
                                                        {{ strtoupper(substr($rating->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="review-user-meta">
                                                        <strong class="review-author">{{ $rating->user->name }}</strong>
                                                        <small class="review-date">
                                                            <i class="bi bi-calendar3"></i>
                                                            <span>{{ $rating->created_at->format('M d, Y') }}</span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="review-rating-summary">
                                                    <div class="stars review-stars">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="bi {{ $i <= $rating->rating ? 'bi-star-fill' : 'bi-star' }} star"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="review-rating-badge">{{ number_format((float) $rating->rating, 1) }}/5</span>
                                                </div>
                                            </div>
                                            @if ($rating->review)
                                                <blockquote class="review-quote">
                                                    <p>{{ $rating->review }}</p>
                                                </blockquote>
                                            @else
                                                <p class="review-empty">No review text provided</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No reviews yet. Be the first to rate this product!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Related Products -->
        @if ($relatedProducts->count() > 0)
            <section class="related-products">
                <h2 class="related-title">You might also like</h2>
                <div class="products-grid">
                    @foreach ($relatedProducts as $relatedProduct)
                        <article class="product-card">
                            <div class="product-image">
                                <a href="{{ route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug]) }}" class="text-decoration-none">
                                    <img src="{{ $relatedProduct->thumbnail ? asset('storage/' . $relatedProduct->thumbnail) : asset('img/logo.png') }}"
                                        alt="{{ $relatedProduct->name }}" loading="lazy">
                                </a>
                                <div class="product-badges">
                                    @if($relatedProduct->created_at->diffInDays(now()) <= 7)
                                        <span class="product-badge badge-new">New</span>
                                    @endif
                                </div>
                            </div>

                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="{{ route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug]) }}" class="text-decoration-none">
                                        {{ $relatedProduct->name }}
                                    </a>
                                </h3>

                                @if($relatedProduct->description?->description)
                                    <p class="product-description">
                                        {{ Str::limit($relatedProduct->description->description, 60) }}
                                    </p>
                                @endif

                                <div class="product-prices">
                                    <span class="product-price">Tsh {{ number_format((float) $relatedProduct->new_price, 0) }}</span>
                                    @if($relatedProduct->old_price && $relatedProduct->old_price > $relatedProduct->new_price)
                                        <span class="product-old-price">Tsh {{ number_format((float) $relatedProduct->old_price, 0) }}</span>
                                    @endif
                                </div>

                                <div class="product-rating">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($relatedProduct->rate > 0)
                                                <i class="bi {{ $i <= round($relatedProduct->rate) ? 'bi-star-fill' : 'bi-star' }} star"></i>
                                            @else
                                                <i class="bi bi-star star text-secondary"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="rating-count">({{ number_format((float) $relatedProduct->rate, 1) }})</span>
                                    <span class="stock-status {{ $relatedProduct->stock > 10 ? 'stock-in' : ($relatedProduct->stock > 0 ? 'stock-low' : 'stock-out') }}">
                                        @if($relatedProduct->stock > 10)
                                            In Stock: {{ $relatedProduct->stock }}
                                        @elseif($relatedProduct->stock > 0)
                                            In Stock: {{ $relatedProduct->stock }}
                                        @else
                                            Out of Stock
                                        @endif
                                    </span>
                                </div>

                                <div class="product-meta">
                                    <span class="category">
                                        <i class="bi bi-tag-fill"></i> {{ $relatedProduct->category->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rating-modal-content">
                <div class="modal-header rating-modal-header">
                    <h5 class="modal-title rating-modal-title" id="ratingModalLabel">
                        <i class="bi bi-star-fill"></i>
                        <span>Rate this Product</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('shop.rate', ['public_id' => $product->public_id, 'slug' => $product->slug]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Your Rating</label>
                            <div class="rating-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <input type="radio" id="star{{ $i }}" name="rating"
                                        value="{{ $i }}" class="d-none" required>
                                    <label for="star{{ $i }}" class="bi bi-star-fill star-rating"></label>
                                @endfor
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review" class="form-label">Your Review (Optional)</label>
                            <textarea class="form-control" id="review" name="review" rows="3"
                                placeholder="Share your thoughts about this product..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn rating-submit-btn" id="submitRatingBtn">
                            <i class="bi bi-send"></i> Submit Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $currentUserRole = auth()->check() ? auth()->user()->role : null;
        $currentUserDashboardUrl = route('login');
        if (auth()->check()) {
            $currentUserDashboardUrl = match (auth()->user()->role) {
                'admin' => route('admin.dashboard'),
                'seller' => route('seller.dashboard'),
                default => route('customer.dashboard'),
            };
        }
    @endphp
    <script>
        window.productId = {{ $product->id }};
        window.productPublicId = @json($product->public_id);
        window.productSlug = @json($product->slug);
        window.productViewActivityUrl = @json(route('shop.view.activity', ['public_id' => $product->public_id, 'slug' => $product->slug]));
        window.currentUserRole = @json($currentUserRole);
        window.currentUserEmailVerified = @json(auth()->check() && auth()->user()->hasVerifiedEmail());
        window.currentUserDashboardUrl = @json($currentUserDashboardUrl);
    </script>

    {{-- script section --}}
@section('scripts')
    <script src="{{ asset('js/show.js') }}"></script>
    <script src="{{ asset('js/show-rating.js') }}"></script>
@endsection

@endsection
