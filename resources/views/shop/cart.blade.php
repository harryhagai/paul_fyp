@extends('layouts.shop')

@section('title', 'Shopping Cart - KidsStore')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endsection

@section('content')
    @php
        $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
        $headerLogo = isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png');
        $hasOutOfStockItems = $cartItems->contains(fn ($item) => (int) $item->product->stock <= 0);
    @endphp

    <main class="shop-container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <section class="cart-page-header mb-4">
                    <div class="cart-header-start">
                        <a href="{{ route('shop') }}" class="cart-header-logo" aria-label="Go to shop home">
                            <img src="{{ $headerLogo }}" alt="KidsStore Logo">
                        </a>
                        <h1 class="cart-title mb-0">My Shopping Cart</h1>
                    </div>
                    <p class="cart-subtitle mb-0">
                        {{ $cartItems->count() > 0 ? $cartItems->count() . ' products • ' . $cartItems->sum('quantity') . ' items in your cart' : 'Your shopping bag is currently empty' }}
                    </p>
                    @if ($cartItems->count() > 0)
                        <a href="{{ route('shop') }}" class="btn btn-theme-outline cart-continue-btn cart-dot-btn" data-spin-link="1">
                            <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-plus-circle"></i> Continue Shopping</span>
                        </a>
                    @endif
                </section>

                @if ($cartItems->count() > 0)
                    @if ($hasOutOfStockItems)
                        <div class="alert alert-warning" role="alert">
                            Some products in your cart are out of stock. Remove them before proceeding to order.
                        </div>
                    @endif
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-lg-8 order-1 order-lg-1">
                            <div class="cart-items-grid">
                                @foreach ($cartItems as $item)
                                    <article class="cart-item-card" data-cart-item-id="{{ $item->public_id }}"
                                        data-product-id="{{ $item->product_id }}" data-price="{{ $item->price }}"
                                        data-stock="{{ $item->product->stock }}">
                                        <div class="cart-item-image-wrap">
                                            <img src="{{ $item->product->thumbnail
                                                ? asset('storage/' . $item->product->thumbnail)
                                                : ($item->product->media->where('is_primary', true)->first()
                                                    ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                    : asset('img/logo.png')) }}"
                                                alt="{{ $item->product->name }}" class="cart-item-image">
                                        </div>

                                        <div class="cart-item-content">
                                            <h2 class="cart-item-title">
                                                <a href="{{ route('shop.show', ['public_id' => $item->product->public_id, 'slug' => $item->product->slug]) }}">{{ $item->product->name }}</a>
                                            </h2>
                                            @if ((int) $item->product->stock <= 0)
                                                <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                                    This product is out of stock. Remove it before proceeding to order.
                                                </div>
                                            @endif
                                            <p class="cart-item-price mb-2">{{ format_money_short($item->price, 2) }}</p>

                                            <div class="cart-item-controls">
                                                <div class="quantity-control">
                                                    <button class="btn btn-sm quantity-btn" onclick="updateQuantity('{{ $item->public_id }}', -1)"
                                                        aria-label="Decrease quantity">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="number" min="1" max="{{ $item->product->stock }}"
                                                        value="{{ $item->quantity }}" class="form-control form-control-sm quantity-input"
                                                        oninput="previewQuantityChange(this)"
                                                        onchange="validateAndUpdateQuantity(this, '{{ $item->public_id }}', {{ $item->product->stock }})"
                                                        >
                                                    <button class="btn btn-sm quantity-btn" onclick="updateQuantity('{{ $item->public_id }}', 1)"
                                                        aria-label="Increase quantity">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>

                                                <button class="btn btn-sm btn-outline-danger btn-remove cart-dot-btn" onclick="removeItem('{{ $item->public_id }}')"
                                                    data-item-id="{{ $item->public_id }}">
                                                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                    <span class="button-text"><i class="bi bi-trash"></i> Remove</span>
                                                </button>
                                            </div>

                                            <div class="cart-item-total">
                                                <span class="cart-item-total-value">Total : {{ format_money_short($item->price * $item->quantity, 2) }}</span>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 order-2 order-lg-2">
                            <aside class="card cart-summary-card sticky-lg-top" style="top: 1rem;">
                                <div class="card-body">
                                    <h5 class="mb-3">Cart Summary</h5>

                                    <div class="summary-line">
                                        <span class="cart-summary-subtotal">Subtotal ({{ $cartItems->sum('quantity') }} items)</span>
                                        <span class="cart-summary-subtotal-amount">{{ format_money_short($subtotal, 2) }}</span>
                                    </div>
                                    <hr>

                                    <div class="summary-total">
                                        <span>Total</span>
                                        <span class="cart-summary-total">{{ format_money_short($total, 2) }}</span>
                                    </div>

                                    <div class="summary-actions mt-4">
                                        <button class="btn btn-theme-outline-danger w-100 clear-cart-btn cart-dot-btn" onclick="clearCart(this)">
                                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                            <span class="button-text"><i class="bi bi-trash me-1"></i> Clear Cart</span>
                                        </button>
                                        @if ($hasOutOfStockItems)
                                            <button type="button" class="btn btn-secondary w-100" disabled>
                                                <span class="button-text"><i class="bi bi-exclamation-triangle me-1"></i> Out of Stock Items</span>
                                            </button>
                                        @else
                                            <a href="{{ route('checkout.index') }}" class="btn btn-theme w-100 cart-dot-btn" data-spin-link="1">
                                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text"><i class="bi bi-credit-card me-1"></i> Proceed To Order</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <div class="cart-mobile-actions" aria-label="Mobile checkout actions">
                        <button class="btn btn-theme-outline-danger clear-cart-btn cart-dot-btn" onclick="clearCart(this)">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-trash me-1"></i> Clear Cart</span>
                        </button>
                        @if ($hasOutOfStockItems)
                            <button type="button" class="btn btn-secondary" disabled>
                                <span class="button-text"><i class="bi bi-exclamation-triangle me-1"></i> Out of Stock Items</span>
                            </button>
                        @else
                            <a href="{{ route('checkout.index') }}" class="btn btn-theme cart-dot-btn" data-spin-link="1">
                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text"><i class="bi bi-credit-card me-1"></i> Proceed To Order</span>
                            </a>
                        @endif
                    </div>
                @else
                    <section class="cart-empty-state">
                        <div class="cart-empty-inner">
                            <div class="cart-empty-icon-wrap">
                                <i class="bi bi-bag-x"></i>
                            </div>
                            <p class="cart-empty-kicker mb-2">No Items Yet</p>
                            <h3>Your cart is empty</h3>
                            <p class="cart-empty-copy">Looks like you haven't added any items yet. Explore products and start building your order.</p>
                            <a href="{{ route('shop') }}" class="btn btn-theme cart-empty-cta cart-dot-btn" data-spin-link="1">
                                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text"><i class="bi bi-grid me-1"></i> Start Shopping</span>
                            </a>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="{{ asset('js/cart.js') }}"></script>
@endsection
