@foreach ($categories as $category)
    <tr data-category-id="{{ $category->public_id }}">
        <td class="category-name-col">{{ $category->name }}</td>
        <td class="category-description-col">{{ $category->description ?: 'No description' }}</td>
        <td class="text-center fit-content-col">
            <span class="badge category-count-badge">{{ $category->products_count }}</span>
        </td>
        <td class="fit-content-col">{{ $category->created_at->format('d M Y') }}</td>
        <td class="text-center fit-content-col text-nowrap">
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="showCategory(@js($category->public_id))">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary themed-outline-btn me-1"
                onclick="editCategory(@js($category->public_id))">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(@js($category->public_id), @js($category->name))">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@endforeach
