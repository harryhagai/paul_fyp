@forelse($orders as $order)
    <tr>
        <td class="fw-semibold font-monospace">{{ $order->order_number ?: $order->public_id }}</td>
        <td class="orders-col-date">{{ optional($order->ordered_at)->format('M d, Y H:i') }}</td>
        <td>
            @php
                $itemsSummary = $order->orderItems->map(function ($item) {
                    $name = $item->product->name ?? 'Unavailable product';
                    return \Illuminate\Support\Str::limit($name, 24) . ' (' . $item->quantity . ')';
                });
                $visibleItems = $itemsSummary->take(2);
                $hiddenItemsCount = max(0, $itemsSummary->count() - $visibleItems->count());
                $totalItems = (int) $order->orderItems->sum('quantity');
            @endphp
            <div class="d-inline d-md-none fw-semibold">{{ $totalItems }}</div>
            <div class="order-items-compact d-none d-md-flex" title="{{ $itemsSummary->implode(', ') }}">
                @foreach($visibleItems as $itemText)
                    <span class="order-item-chip">{{ $itemText }}</span>
                @endforeach
                @if($hiddenItemsCount > 0)
                    <span class="order-item-more">+{{ $hiddenItemsCount }} more</span>
                @endif
            </div>
        </td>
        <td class="fw-semibold">{{ format_money_short($order->total_amount, 2) }}</td>
        <td>
            <span class="badge text-capitalize {{ $order->status === 'cancelled' ? 'status-badge-soft-danger' : 'text-bg-light border' }}">{{ $order->status_text }}</span>
            @if($order->payment_status)
                <span class="badge text-capitalize {{ $order->payment_status === 'paid' ? 'text-bg-success' : ($order->payment_status === 'failed' ? 'text-bg-danger' : 'text-bg-warning') }}">
                    {{ $order->payment_status }}
                </span>
            @endif
        </td>
        <td class="text-end orders-actions-cell">
            <div class="btn-group" role="group">
                <a href="{{ route('customer.order.details', $order) }}" class="btn btn-sm btn-outline-primary themed-outline-btn view-order-btn" aria-label="View order {{ $order->order_number }}">
                    <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="btn-text"><i class="bi bi-eye me-1"></i>View</span>
                </a>
                @if($order->canBeCancelled())
                    <button type="button" class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="{{ $order->public_id }}" data-order-number="{{ $order->order_number }}">
                        <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                        <span class="btn-text"><i class="bi bi-x-circle me-1"></i>Cancel</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr class="orders-empty-row">
        <td colspan="6">
            <div class="empty-state">
                <i class="bi bi-receipt-cutoff"></i>
                <p class="mb-0">No orders found.</p>
                <small class="text-muted">Try changing your search, status, or date filters.</small>
            </div>
        </td>
    </tr>
@endforelse
