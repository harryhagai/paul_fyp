<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Display all categories
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'summary_html' => view('seller.partials.category_summary_items', compact('categories'))->render(),
                'table_html' => view('seller.partials.category_rows', compact('categories'))->render(),
                'next_page_url' => $categories->nextPageUrl(),
            ]);
        }

        return view('seller.categories', compact('categories'));
    }

    // Store a new category
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category->load('products')->loadCount('products'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: '.$e->getMessage(),
            ], 500);
        }
    }

    // Show category details
    public function show($id)
    {
        $category = Category::with('products')->withCount('products')
            ->wherePublicIdOrId($id)
            ->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    // Update category
    public function update(Request $request, $id)
    {
        $category = Category::wherePublicIdOrId($id)->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191|unique:categories,name,'.$id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category->load('products')->loadCount('products'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete category
    public function destroy($id)
    {
        $category = Category::withCount('products')
            ->wherePublicIdOrId($id)
            ->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Check if category has products
        if ($category->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that contains products. Please remove all products first.',
            ], 422);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: '.$e->getMessage(),
            ], 500);
        }
    }
}
