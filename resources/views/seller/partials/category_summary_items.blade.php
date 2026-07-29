@foreach ($categories as $category)
    <div class="category-summary-item" data-category-id="{{ $category->public_id }}">
        <div class="category-summary-card h-100">
            <i
                class="bi bi-{{ $category->products_count > 0 ? 'box-seam' : 'folder' }} category-summary-watermark"></i>
            <div class="category-summary-name">{{ $category->name }}</div>
            <div class="category-summary-value">{{ number_format($category->products_count) }} Products</div>
        </div>
    </div>
@endforeach
