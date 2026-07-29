@forelse($orders as $order)
@php
    $currentStatus = strtolower((string) $order->status);
    $statusFlow = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];
    $allowedNextStatuses = $statusFlow[$currentStatus] ?? [];
@endphp
<tr>
    <td>
        @php $orderNumber = $order->order_number ?: $order->public_id; @endphp
        <strong class="font-monospace" title="{{ $orderNumber }}">{{ $orderNumber }}</strong>
    </td>
    <td>
        <div class="customer-info">
            <div class="customer-avatar">
                {{ strtoupper(substr($order->user->name, 0, 1)) }}
            </div>
            <div>
                <div class="fw-bold">{{ $order->user->name }}</div>
                <small class="text-muted">{{ $order->user->email }}</small>
                <div><small class="text-muted">{{ $order->user->phone_number ?: 'N/A' }}</small></div>
            </div>
        </div>
    </td>
    <td>
        {{ $order->orderItems->count() }} item{{ $order->orderItems->count() > 1 ? 's' : '' }}
    </td>
    <td>
        <strong>{{ format_money_short($order->total_amount, 0) }}</strong>
    </td>
    <td>
        <span class="order-status-badge status-{{ $order->status }}">
            {{ ucfirst($order->status) }}
        </span>
    </td>
    <td>
        <div class="small text-muted">
            {{ $order->created_at->format('d M Y') }}
        </div>
        <div class="small">
            {{ $order->created_at->format('H:i') }}
        </div>
    </td>
    <td>
        <div class="d-flex gap-1 align-items-start flex-wrap">
            <button class="btn btn-sm btn-outline-primary themed-outline-btn action-btn view-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewOrderModal"
                    data-order-id="{{ $order->public_id }}">
                <i class="bi bi-eye me-1"></i>View
            </button>
            @foreach($allowedNextStatuses as $nextStatus)
                @php
                    $btnClass = match($nextStatus) {
                        'confirmed' => 'btn-outline-success',
                        'completed' => 'btn-outline-success',
                        'cancelled' => 'btn-outline-danger',
                        default => 'btn-outline-secondary',
                    };
                    $btnIcon = match($nextStatus) {
                        'confirmed' => 'bi-patch-check',
                        'completed' => 'bi-check2-circle',
                        'cancelled' => 'bi-x-circle',
                        default => 'bi-arrow-right-circle',
                    };
                    $btnLabel = match($nextStatus) {
                        'confirmed' => 'Confirm',
                        'completed' => 'Complete',
                        'cancelled' => 'Cancel',
                        default => ucfirst($nextStatus),
                    };
                @endphp
                <button type="button"
                        class="btn btn-sm action-btn status-action-btn {{ $btnClass }}"
                        data-order-id="{{ $order->public_id }}"
                        data-current-status="{{ $currentStatus }}"
                        data-new-status="{{ $nextStatus }}"
                        data-order-number="{{ $orderNumber }}">
                    <i class="bi {{ $btnIcon }} me-1"></i>{{ $btnLabel }}
                </button>
            @endforeach
            @if(empty($allowedNextStatuses))
                <span class="badge text-bg-light border">Status locked</span>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4">
        <div class="orders-empty-state mx-auto">
            <div class="orders-empty-icon-wrap">
                <i class="bi bi-receipt-cutoff orders-empty-icon"></i>
            </div>
            <h6 class="orders-empty-title mb-1">No orders found</h6>
            <p class="orders-empty-text mb-0">Try changing search filters or date range.</p>
        </div>
    </td>
</tr>
@endforelse
