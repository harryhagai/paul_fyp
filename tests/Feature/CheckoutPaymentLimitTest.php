<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutPaymentLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_amount_outside_clickpesa_limit_without_creating_order(): void
    {
        config([
            'clickpesa.min_amount' => 500,
            'clickpesa.max_amount' => 3000000,
        ]);

        Http::fake();

        $user = User::factory()->create([
            'role' => 'customer',
            'phone_number' => '+255765384905',
        ]);

        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Small Item',
            'slug' => 'small-item',
            'new_price' => 250,
            'stock' => 10,
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 250,
        ]);

        $this->actingAs($user)
            ->postJson(route('checkout.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'customer@example.com',
                'phone_number' => '+255765384905',
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'AMOUNT_OUT_OF_RANGE',
                'action' => 'back_to_cart',
            ]);

        $this->assertSame(0, Order::count());
        $this->assertSame(1, Cart::count());
        $this->assertSame(1, CartItem::count());
        $this->assertSame(10, $product->fresh()->stock);

        Http::assertNothingSent();
    }
}
