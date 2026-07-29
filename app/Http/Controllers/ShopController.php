<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\Rating;

class ShopController extends Controller
{
    private const MIN_VIEW_SECONDS_TO_RECORD = 10;

    public function index(Request $request)
    {
        $query = Product::with(['category', 'media', 'description']);

        // Only show products with stock > 0
        $query->where('stock', '>', 0);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%')
                  ->orWhereHas('description', function($desc) use ($search) {
                      $desc->where('description', 'like', '%' . $search . '%')
                          ->orWhere('specifications', 'like', '%' . $search . '%')
                          ->orWhere('details', 'like', '%' . $search . '%');
                  });
            });
        }
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', (int)$request->category);
        }

        // Rating filter
        if ($request->has('rating') && !empty($request->rating)) {
            $query->where('rate', '>=', $request->rating);
        }

        // Discount filter
        if ($request->has('on_sale') && $request->on_sale == '1') {
            $query->where('discount', '>', 0);
        }

        // Sorting
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order;

            if (in_array($sortBy, ['name', 'new_price', 'created_at', 'rate']) && in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderByDesc('created_at');
        }

        $products = $query->paginate(24)->withQueryString();

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }])->orderBy('name')->get();

        return view('shop', compact('products', 'categories'));
    }

    public function show(string $publicId, string $slug)
    {
        $product = $this->resolveShopProduct($publicId, ['category', 'media', 'description', 'ratings.user']);

        if ($slug !== $product->slug) {
            return redirect()->route('shop.show', [
                'public_id' => $product->public_id,
                'slug' => $product->slug,
            ], 301);
        }

        // Increment view count
        $product->increment('views');

        // Get related products from same category
        $relatedProducts = Product::with(['category', 'media', 'description'])
                                ->where('category_id', $product->category_id)
                                ->where('id', '!=', $product->id)
                                ->where('stock', '>', 0)
                                ->take(4)
                                ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }

    public function trackViewActivity(Request $request, string $publicId, string $slug)
    {
        $request->validate([
            'event' => 'nullable|string|in:view_start,heartbeat,view_end',
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
        ]);

        $product = $this->resolveShopProduct($publicId, [], ['id']);
        $duration = (int) ($request->integer('duration_seconds', 0));
        $event = $request->input('event', 'heartbeat');
        $sessionId = $request->session()->getId();

        if ($duration < self::MIN_VIEW_SECONDS_TO_RECORD) {
            return response()->json(['success' => true, 'skipped' => true]);
        }

        $existing = ProductView::where('product_id', $product->id)
            ->where('session_id', $sessionId)
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->first();

        if (!$existing) {
            ProductView::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'view_count' => in_array($event, ['view_start', 'view_end'], true) ? 1 : 0,
                'viewed_seconds' => $duration,
                'last_activity_at' => now(),
            ]);
        } else {
            $existing->update([
                'user_id' => $existing->user_id ?: Auth::id(),
                'view_count' => $existing->view_count + (in_array($event, ['view_start', 'view_end'], true) ? 1 : 0),
                'viewed_seconds' => max($existing->viewed_seconds, $duration),
                'last_activity_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function categories(Request $request)
    {
        $query = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }]);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $categories = $query->orderBy('name')->get();

        return view('categories', compact('categories'));
    }

    public function category(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = Product::with(['category', 'media', 'description'])
                       ->where('category_id', $category->id)
                       ->where('stock', '>', 0);

        // Search within category
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%')
                  ->orWhereHas('description', function($desc) use ($search) {
                      $desc->where('description', 'like', '%' . $search . '%')
                          ->orWhere('specifications', 'like', '%' . $search . '%')
                          ->orWhere('details', 'like', '%' . $search . '%');
                  });
            });
        }

        // Rating filter
        if ($request->has('rating') && !empty($request->rating)) {
            $query->where('rate', '>=', $request->rating);
        }

        // Discount filter
        if ($request->has('on_sale') && $request->on_sale == '1') {
            $query->where('discount', '>', 0);
        }

        // Sorting
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order;

            if (in_array($sortBy, ['name', 'new_price', 'created_at', 'rate'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(24)->withQueryString();

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }])->orderBy('name')->get();

        return view('category', compact('category', 'products', 'categories'));
    }

    public function storeRating(Request $request, string $publicId, string $slug)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You must be logged in to rate products.'], 401);
            }
            return redirect()->back()->with('error', 'You must be logged in to rate products.');
        }

        $product = $this->resolveShopProduct($publicId);

        // Check if user already rated this product
        $existingRating = Rating::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();

        if ($existingRating) {
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
        } else {
            Rating::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
        }

        // Update product's average rating
        $averageRating = $product->ratings()->avg('rating');
        $product->update(['rate' => $averageRating]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Thank you for your rating!']);
        }

        return back()->with('success', 'Thank you for your rating!');
    }

    private function resolveShopProduct(string $publicId, array $with = [], array $select = ['*']): Product
    {
        $query = Product::query()->with($with)->select($select);
        return $query->where('public_id', $publicId)->firstOrFail();
    }

}
