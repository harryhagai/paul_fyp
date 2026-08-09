<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductMediaDeleteAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_delete_all_gallery_media_for_one_product_only(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::create([
            'name' => 'Media '.Str::random(8),
            'slug' => 'media-'.Str::lower(Str::random(8)),
        ]);

        $product = $this->createProduct($category, 'products/thumbnail.jpg');
        $otherProduct = $this->createProduct($category);

        Storage::disk('public')->put('products/thumbnail.jpg', 'thumbnail');
        Storage::disk('public')->put('products/media/image.jpg', 'image');
        Storage::disk('public')->put('products/media/video.mp4', 'video');
        Storage::disk('public')->put('products/media/other.jpg', 'other');

        $image = ProductMedia::create([
            'product_id' => $product->id,
            'type' => 'image',
            'file_path' => 'products/media/image.jpg',
            'is_primary' => true,
        ]);
        $video = ProductMedia::create([
            'product_id' => $product->id,
            'type' => 'video',
            'file_path' => 'products/media/video.mp4',
            'is_primary' => false,
        ]);
        $otherMedia = ProductMedia::create([
            'product_id' => $otherProduct->id,
            'type' => 'image',
            'file_path' => 'products/media/other.jpg',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($seller)->deleteJson(route('seller.products.media.deleteAll', [
            'productId' => $product->public_id,
        ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'deleted_count' => 2,
                'file_cleanup_warning' => false,
            ]);

        $this->assertDatabaseMissing('product_media', ['id' => $image->id]);
        $this->assertDatabaseMissing('product_media', ['id' => $video->id]);
        $this->assertDatabaseHas('product_media', ['id' => $otherMedia->id]);

        Storage::disk('public')->assertMissing('products/media/image.jpg');
        Storage::disk('public')->assertMissing('products/media/video.mp4');
        Storage::disk('public')->assertExists('products/media/other.jpg');
        Storage::disk('public')->assertExists('products/thumbnail.jpg');
    }

    private function createProduct(Category $category, ?string $thumbnail = null): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => 'Product '.Str::random(8),
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'new_price' => 1000,
            'stock' => 10,
            'thumbnail' => $thumbnail,
        ]);
    }
}
