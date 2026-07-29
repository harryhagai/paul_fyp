<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductMedia;
use App\Models\ProductDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Display all products with pagination
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category_id = $request->input('category_id');

        $query = Product::query()
            ->with(['category:id,name'])
            ->select([
                'id',
                'public_id',
                'category_id',
                'name',
                'slug',
                'thumbnail',
                'new_price',
                'old_price',
                'discount',
                'rate',
                'stock',
                'is_advertised',
                'created_at',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->when($category_id, function ($q) use ($category_id) {
                $q->where('category_id', $category_id);
            })
            ->orderBy('created_at', 'desc');

        $products = $query->paginate(10)->withQueryString();

        $categories = Category::select('id', 'name')->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('seller.partials.product_rows', compact('products'))->render(),
                'next_page_url' => $products->nextPageUrl(),
            ]);
        }

        return view('seller.products', compact('products', 'categories'));
    }

    // Show create product form
    public function create()
    {
        $categories = Category::all();
        return view('seller.product-create', compact('categories'));
    }

    // Show edit product form
    public function edit($id)
    {
        $product = Product::with(['category', 'description', 'media'])
            ->wherePublicIdOrId($id)
            ->firstOrFail();
        $categories = Category::all();
        return view('seller.product-edit', compact('product', 'categories'));
    }

    // Store a new product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:191',
            'old_price' => 'nullable|numeric|min:0',
            'new_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_advertised' => 'boolean',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Generate unique slug
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create product
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $slug,
                'old_price' => $request->old_price,
                'new_price' => $request->new_price,
                'discount' => $request->old_price ? round((($request->old_price - $request->new_price) / $request->old_price) * 100) : 0,
                'rate' => 0,
                'stock' => $request->stock,
                'is_advertised' => $request->is_advertised ?? false,
            ]);

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('products/thumbnails', 'public');
                $product->update(['thumbnail' => $thumbnailPath]);
            }

            // Create product description
            ProductDescription::create([
                'product_id' => $product->id,
                'description' => $request->description,
                'specifications' => $request->specifications,
                'details' => $request->details,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product->load(['category', 'media'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show product details
    public function show($id)
    {
        $product = Product::with(['category', 'media', 'description'])
            ->wherePublicIdOrId($id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    // Update product
    public function update(Request $request, $id)
    {
        $product = Product::wherePublicIdOrId($id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Check if this is a rating-only update
        if ($request->has('rate') && count($request->all()) === 2 && $request->has('_method')) {
            // Only update rating
            $validator = Validator::make($request->all(), [
                'rate' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update([
                'rate' => $request->rate,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product rating updated successfully!',
                'product' => $product->load(['category', 'media'])
            ]);
        }

        // Full product update
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:191',
            'old_price' => 'nullable|numeric|min:0',
            'new_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_advertised' => 'boolean',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Generate unique slug (exclude current product)
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Update product
            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $slug,
                'old_price' => $request->old_price,
                'new_price' => $request->new_price,
                'discount' => $request->old_price ? round((($request->old_price - $request->new_price) / $request->old_price) * 100) : 0,
                'stock' => $request->stock,
                'is_advertised' => $request->is_advertised ?? false,
                'rate' => $request->rate ?? $product->rate ?? 0,
            ]);

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($product->thumbnail && Storage::exists('public/' . $product->thumbnail)) {
                    Storage::delete('public/' . $product->thumbnail);
                }

                $thumbnailPath = $request->file('thumbnail')->store('products/thumbnails', 'public');
                $product->update(['thumbnail' => $thumbnailPath]);
            }

            // Update product description
            $product->description()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'description' => $request->description,
                    'specifications' => $request->specifications,
                    'details' => $request->details,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product->load(['category', 'media'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete product
    public function destroy($id)
    {
        $product = Product::wherePublicIdOrId($id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Delete thumbnail if exists
            if ($product->thumbnail && Storage::exists('public/' . $product->thumbnail)) {
                Storage::delete('public/' . $product->thumbnail);
            }

            // Delete related media
            $product->media()->delete();

            // Delete description
            $product->description()->delete();

            // Delete product
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show product media management page
    public function media($id)
    {
        $product = Product::with(['media'])->wherePublicIdOrId($id)->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        return view('seller.product-media', compact('product'));
    }

    // Upload media files
    public function uploadMedia(Request $request, $productId)
    {
        $product = Product::wherePublicIdOrId($productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        try {
            $toBytes = static function (string $value): int {
                $value = trim($value);
                if ($value === '') return 0;
                $unit = strtolower(substr($value, -1));
                $num = (float) $value;
                return match ($unit) {
                    'g' => (int) ($num * 1024 * 1024 * 1024),
                    'm' => (int) ($num * 1024 * 1024),
                    'k' => (int) ($num * 1024),
                    default => (int) $num,
                };
            };
            $uploadLimit = ini_get('upload_max_filesize') ?: '2M';
            $postLimit = ini_get('post_max_size') ?: '8M';
            $postLimitBytes = $toBytes($postLimit);
            $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);

            if ($postLimitBytes > 0 && $contentLength > $postLimitBytes) {
                return response()->json([
                    'success' => false,
                    'message' => "Request too large for server limits. Current limits: upload_max_filesize={$uploadLimit}, post_max_size={$postLimit}.",
                ], 422);
            }

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => "No file received by server. Current limits: upload_max_filesize={$uploadLimit}, post_max_size={$postLimit}. Increase PHP limits for video uploads.",
                ], 422);
            }

            $file = $request->file('file');
            $maxBytes = 50 * 1024 * 1024; // 50MB effective media size
            $isChunked = $request->filled('upload_id') && $request->filled('chunk_index') && $request->filled('total_chunks');

            if ($isChunked) {
                $uploadId = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $request->input('upload_id'));
                $chunkIndex = (int) $request->input('chunk_index');
                $totalChunks = (int) $request->input('total_chunks');
                $originalName = (string) $request->input('original_name', $file->getClientOriginalName());
                $mime = strtolower((string) $request->input('mime_type', $file->getMimeType()));

                if ($uploadId === '' || $totalChunks < 1 || $chunkIndex < 0 || $chunkIndex >= $totalChunks) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid chunk metadata.',
                    ], 422);
                }

                $chunkDir = storage_path('app/chunks');
                if (!is_dir($chunkDir)) {
                    mkdir($chunkDir, 0775, true);
                }

                $tempPath = $chunkDir . DIRECTORY_SEPARATOR . $uploadId . '.part';
                $metaPath = $chunkDir . DIRECTORY_SEPARATOR . $uploadId . '.json';
                $chunkData = file_get_contents($file->getRealPath());
                if ($chunkData === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to read upload chunk.',
                    ], 422);
                }

                file_put_contents($tempPath, $chunkData, FILE_APPEND);

                if (!file_exists($metaPath)) {
                    file_put_contents($metaPath, json_encode([
                        'original_name' => $originalName,
                        'mime' => $mime,
                    ]));
                }

                if ($chunkIndex < ($totalChunks - 1)) {
                    return response()->json([
                        'success' => true,
                        'chunk_received' => $chunkIndex + 1,
                        'total_chunks' => $totalChunks,
                        'message' => 'Chunk uploaded',
                    ]);
                }

                if (!file_exists($tempPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assembled file was not found.',
                    ], 422);
                }

                $assembledSize = filesize($tempPath);
                if ($assembledSize === false || $assembledSize <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to assemble uploaded file.',
                    ], 422);
                }

                if ($assembledSize > $maxBytes) {
                    @unlink($tempPath);
                    @unlink($metaPath);
                    return response()->json([
                        'success' => false,
                        'message' => 'File too large. Maximum allowed size is 50MB.',
                    ], 422);
                }

                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowedImageExt = ['jpg', 'jpeg', 'png', 'gif'];
                $isImage = str_starts_with($mime, 'image/') || in_array($ext, $allowedImageExt, true);
                $type = $isImage ? 'image' : 'video';

                $safeExt = $ext !== '' ? $ext : ($type === 'image' ? 'jpg' : 'mp4');
                $filename = time() . '_' . uniqid() . '.' . $safeExt;
                $path = 'products/media/' . $filename;
                Storage::disk('public')->put($path, fopen($tempPath, 'r'));

                @unlink($tempPath);
                @unlink($metaPath);
            } else {
                if ($file->getSize() > $maxBytes) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File too large. Maximum allowed size is 50MB.',
                    ], 422);
                }

                $mime = strtolower((string) $file->getMimeType());
                $ext = strtolower((string) $file->getClientOriginalExtension());

                $allowedImageExt = ['jpg', 'jpeg', 'png', 'gif'];
                $isImage = str_starts_with($mime, 'image/') || in_array($ext, $allowedImageExt, true);
                $type = $isImage ? 'image' : 'video';
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products/media', $filename, 'public');
            }

            // First uncheck all primary media for this product
            ProductMedia::where('product_id', $product->id)->update(['is_primary' => false]);

            // Create media record
            $media = ProductMedia::create([
                'product_id' => $product->id,
                'type' => $type,
                'file_path' => $path,
                'is_primary' => true, // Set first upload as primary by default
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media uploaded successfully',
                'media' => $media
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Set media as primary
    public function setPrimaryMedia(Request $request, $productId, $mediaId)
    {
        $product = Product::wherePublicIdOrId($productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $media = ProductMedia::where('product_id', $product->id)->find($mediaId);
        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        }

        try {
            // First uncheck all primary media for this product
            ProductMedia::where('product_id', $product->id)->update(['is_primary' => false]);

            // Set this media as primary
            $media->update(['is_primary' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Media set as primary successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete media
    public function deleteMedia($productId, $mediaId)
    {
        $product = Product::wherePublicIdOrId($productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $media = ProductMedia::where('product_id', $product->id)->find($mediaId);
        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        }

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            // Delete media record
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Toggle advertised status
    public function toggleAdvertised($id)
    {
        $product = Product::wherePublicIdOrId($id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->update([
            'is_advertised' => !$product->is_advertised
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advertised status updated!',
            'is_advertised' => $product->is_advertised
        ]);
    }
}
