@extends('layouts.dashboard')

@section('title', 'Order Details - KidsStore')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/customer-account-clean.css') }}">
@endsection

@section('content')
<div class="container-fluid mt-2 customer-clean-page customer-order-details-page"
     data-order-id="{{ $order->public_id }}"
     data-pay-url="{{ route('customer.orders.pay', $order) }}">
    <div class="d-flex flex-column flex-md-row justify-content-start align-items-start align-items-md-center gap-2 gap-md-3 mb-4 page-heading">
        <div>
            <h1 class="h3 mb-1 page-title"><i class="bi bi-receipt me-2"></i>Order Details</h1>
            <p class="page-subtitle mb-0">Review order #{{ $order->order_number }} and manage pending items.</p>
        </div>
        <div class="order-details-cta">
            <a href="{{ route('customer.orders') }}" class="btn order-details-primary-btn back-to-orders-btn w-100 d-md-inline-flex">
                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="btn-text"><i class="bi bi-arrow-left me-1"></i>Back to Orders</span>
            </a>
        </div>
    </div>

    <div class="card clean-card mb-4">
        <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Order Information</h5>
            <span class="badge text-capitalize {{ $order->status === 'cancelled' ? 'status-badge-soft-danger' : 'text-bg-light border' }}">{{ $order->status_text }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="detail-row">
                        <span class="text-muted">Order Number</span>
                        <span>{{ $order->order_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Order Date</span>
                        <span>{{ optional($order->ordered_at)->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Payment</span>
                        <span class="text-capitalize">{{ $order->payment_status ?? 'pending' }}</span>
                    </div>
                </div>
                @if($order->payment_message)
                    <div class="col-12">
                        <div class="alert alert-light border mb-0">{{ $order->payment_message }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card clean-card mb-4">
        <div class="card-header clean-card-header">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Order Items</h5>
        </div>
        <div class="card-body">
            @if($order->orderItems->isNotEmpty())
                <div class="order-items-grid">
                    @foreach($order->orderItems as $item)
                        @php
                            $product = $item->product;
                            $productName = $item->product_name ?: 'N/A';
                        @endphp
                        <article class="order-item-card" data-order-item-id="{{ $item->public_id }}">
                            <div class="order-item-image-wrap">
                                <img src="{{ $product && $product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png') }}"
                                    alt="{{ $productName }}" class="order-item-image">
                            </div>

                            <div class="order-item-content">
                                <h6 class="order-item-title mb-1">{{ $productName }}</h6>
                                <p class="order-item-price mb-2">{{ format_money_short($item->unit_price, 2) }}</p>

                                <div class="order-item-controls">
                                    @if($order->status === 'pending' && $product)
                                        <form class="d-inline update-quantity-form" data-item-id="{{ $item->public_id }}">
                                            <div class="quantity-control">
                                                <button type="button" class="btn btn-sm quantity-btn themed-outline-btn" data-action="decrease" aria-label="Decrease quantity">
                                                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                    <span class="btn-text"><i class="bi bi-dash"></i></span>
                                                </button>
                                                <input type="number" class="form-control form-control-sm text-center quantity-input" value="{{ $item->quantity }}" min="1" max="{{ $product->stock }}" readonly>
                                                <button type="button" class="btn btn-sm quantity-btn themed-outline-btn" data-action="increase" aria-label="Increase quantity">
                                                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                    <span class="btn-text"><i class="bi bi-plus"></i></span>
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="order-item-qty-text">Qty: {{ $item->quantity }}</div>
                                    @endif

                                    @if($order->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="{{ $item->public_id }}" data-product-name="{{ optional($product)->name ?? 'this item' }}">
                                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                            <span class="btn-text"><i class="bi bi-trash me-1"></i>Remove</span>
                                        </button>
                                    @endif
                                </div>

                                <div class="order-item-total">
                                    <span class="item-total">{{ format_money_short($item->total_price, 2) }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <p class="mb-0">No items found for this order.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">
        @if($order->status === 'pending' && in_array(strtolower((string) ($order->payment_status ?? 'pending')), ['pending', 'processing', 'failed'], true))
            <div class="col-12 col-xl-6">
                <div class="card clean-card h-100">
                    <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h5 class="mb-1 section-title"><i class="bi bi-phone me-2"></i>Payment Action</h5>
                            <p class="text-muted mb-0">Send another mobile money prompt if the first one was missed.</p>
                        </div>
                        <button type="button" class="btn order-details-primary-btn d-inline-flex" id="payOrderBtn">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="btn-text"><i class="bi bi-phone-vibrate me-1"></i>Pay Now</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($order->canBeCancelled())
            <div class="col-12 col-xl-6">
                <div class="card clean-card h-100">
                    <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h5 class="mb-1 section-title"><i class="bi bi-sliders me-2"></i>Order Actions</h5>
                            <p class="text-muted mb-0">Pending orders can still be cancelled.</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger order-cancel-btn d-inline-flex" id="cancelOrderBtn">
                            <i class="bi bi-x-circle me-1"></i>Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12 {{ $order->canBeCancelled() ? 'col-xl-6' : 'col-xl-12' }}">
            <div class="summary-card p-3">
                <h5 class="mb-3 section-title"><i class="bi bi-receipt-cutoff me-2"></i>Order Summary</h5>
                @if($order->discount_amount > 0)
                    <div class="summary-row">
                        <span class="text-muted">Discount</span>
                        <span>-{{ format_money_short($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row summary-total-inline fw-semibold">
                    <span>Total :</span>
                    <span class="js-order-total">{{ format_money_short($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade customer-clean-page-modal" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel"><i class="bi bi-x-circle me-2"></i>Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary themed-outline-btn" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Keep Order
                </button>
                <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                        <span class="btn-text"><i class="bi bi-x-circle me-1"></i>Cancel Order</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade customer-clean-page-modal" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel"><i class="bi bi-trash me-2"></i>Remove Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove <strong id="removeProductName"></strong> from this order?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary themed-outline-btn" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Keep Item
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="btn-text"><i class="bi bi-trash me-1"></i>Remove Item</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/customer-order-details.js') }}"></script>
@endsection
