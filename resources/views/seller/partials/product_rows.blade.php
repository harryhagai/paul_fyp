@forelse($products as $product)
<tr class="product-card">
    <td>{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>
    <td>
        @if($product->thumbnail)
            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="thumbnail-preview thumbnail-clickable" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="{{ $product->public_id }}">
        @else
            <div class="thumbnail-placeholder thumbnail-clickable" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="{{ $product->public_id }}">
                <i class="bi bi-image"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="d-flex flex-column">
            <strong>{{ $product->name }}</strong>
            <small class="text-muted">{{ $product->slug }}</small>
        </div>
    </td>
    <td>
        <div class="d-flex flex-column">
            <strong>{{ format_money_short($product->new_price, 2) }}</strong>
            @if($product->old_price)
                <small class="text-decoration-line-through text-muted">
                    {{ format_money_short($product->old_price, 2) }}
                </small>
            @endif
        </div>
    </td>
    <td>
        {{ $product->stock }}
    </td>
    <td>
        @if($product->robot_location)
            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                <i class="bi bi-robot me-1"></i>LOCATION {{ $product->robot_location }}
            </span>
        @else
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                Not assigned
            </span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('seller.products.media', $product->public_id) }}" class="btn btn-sm btn-outline-success action-btn themed-outline-btn" title="Manage Media"><i class="bi bi-images"></i></a>
            <button class="btn btn-sm btn-outline-primary action-btn themed-outline-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editProductModal" data-product-id="{{ $product->public_id }}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger action-btn delete-btn" data-product-id="{{ $product->public_id }}"><i class="bi bi-trash"></i></button>
            <button class="btn btn-sm btn-outline-info action-btn view-btn" data-bs-toggle="modal" data-bs-target="#viewProductModal" data-product-id="{{ $product->public_id }}"><i class="bi bi-eye"></i></button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4">
        <div class="products-empty-state mx-auto text-center">
            <div class="products-empty-icon-wrap">
                <i class="bi bi-box-seam products-empty-icon"></i>
            </div>
            <h6 class="products-empty-title mb-1">No products found</h6>
            <p class="products-empty-text mb-0">Try changing filters or add a new product.</p>
        </div>
    </td>
</tr>
@endforelse
