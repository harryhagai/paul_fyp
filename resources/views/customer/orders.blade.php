@extends('layouts.dashboard')

@section('title', 'My Orders - KidsStore')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/customer-account-clean.css') }}">
<style>
    .orders-cta {
        width: 100%;
    }

    @media (max-width: 767.98px) {
        .orders-cta {
            display: none;
        }
    }

    @media (min-width: 768px) {
        .orders-cta {
            width: auto;
            margin-left: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid mt-2 customer-clean-page customer-orders-page"
     data-orders-url="{{ route('customer.orders') }}">
    <div class="d-flex flex-column flex-md-row justify-content-start align-items-start align-items-md-center gap-2 gap-md-3 mb-4 page-heading">
        <div>
            <h1 class="h3 mb-1 page-title"><i class="bi bi-receipt me-2"></i>My Orders</h1>
            <p class="page-subtitle mb-0">Track your purchases, view details, and manage pending orders.</p>
        </div>
        <div class="orders-cta row g-2">
            <div class="col-6 col-md-auto">
                <a href="{{ route('shop') }}" class="btn btn-outline-primary themed-outline-btn w-100">
                    <i class="bi bi-shop me-1"></i>Browse Products
                </a>
            </div>
        </div>
    </div>

    <div class="card clean-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.orders') }}" id="ordersFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="ordersSearch" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" id="ordersSearch" name="search" class="form-control" value="{{ request('search') }}" placeholder="Order number, status, product...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select id="statusFilter" name="status" class="form-select">
                            <option value="">All Orders</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="dateFrom" class="form-label">From</label>
                        <input type="date" id="dateFrom" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="dateTo" class="form-label">To</label>
                        <input type="date" id="dateTo" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary themed-outline-btn w-100" id="ordersResetBtn">
                            <i class="bi bi-arrow-repeat me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card clean-card">
        <div class="card-header clean-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Orders List</h5>
            <div class="text-muted small" id="ordersMeta"
                 data-loaded-from="{{ $orders->firstItem() ?? 0 }}"
                 data-loaded-to="{{ $orders->lastItem() ?? 0 }}">
                Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order Number</th>
                            <th class="orders-col-date">Date</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        @include('customer.partials.order_rows', ['orders' => $orders])
                    </tbody>
                </table>
            </div>

            <div id="ordersLoader" class="text-center py-3 d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading orders...
            </div>
            <div id="ordersSentinel" data-next-page-url="{{ $orders->nextPageUrl() }}" aria-hidden="true"></div>
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
                <p>Are you sure you want to cancel order <strong id="cancelOrderNumber"></strong>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary themed-outline-btn" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Keep Order
                </button>
                <form id="cancelOrderForm" method="POST">
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

@endsection

@section('scripts')
<script src="{{ asset('js/customer-orders.js') }}"></script>
@endsection
