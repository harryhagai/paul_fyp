<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\PendingShopAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingShopActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_adds_pending_product_to_cart_and_returns_to_product(): void
    {
        $product = $this->createProduct();
        $returnUrl = route('shop.show', [$product->public_id, $product->slug], false);

        $this->get(route('register', [
            'action' => 'add_to_cart',
            'product_id' => $product->id,
            'quantity' => 2,
            'redirect' => $returnUrl,
        ]))->assertOk();

        $this->post(route('register'), [
            'name' => 'Pending Customer',
            'email' => 'pending.customer@gmail.com',
            'phone_number' => '255712345678',
            'password' => 'secret123',
        ])
            ->assertRedirect($returnUrl)
            ->assertSessionHas('success', 'Product added to cart successfully')
            ->assertSessionMissing(PendingShopAction::SESSION_KEY);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_registration_returns_to_product_and_opens_rating_modal(): void
    {
        $product = $this->createProduct();
        $returnUrl = route('shop.show', [$product->public_id, $product->slug], false);

        $this->get(route('register', [
            'action' => 'rate_product',
            'product_id' => $product->id,
            'redirect' => $returnUrl,
        ]))->assertOk();

        $this->post(route('register'), [
            'name' => 'Rating Customer',
            'email' => 'rating.customer@gmail.com',
            'phone_number' => '255712345679',
            'password' => 'secret123',
        ])
            ->assertRedirect($returnUrl.'?open_rating=1')
            ->assertSessionMissing(PendingShopAction::SESSION_KEY);
    }

    public function test_login_processes_pending_cart_action_without_email_verification(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->unverified()->create([
            'role' => 'customer',
            'phone_number' => '255713000001',
        ]);
        $returnUrl = route('shop.show', [$product->public_id, $product->slug], false);

        $this->get(route('login', [
            'action' => 'add_to_cart',
            'product_id' => $product->id,
            'quantity' => 1,
            'redirect' => $returnUrl,
        ]));

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect($returnUrl);

        $this->assertSame(1, CartItem::where('product_id', $product->id)->count());
    }

    public function test_unverified_customer_can_perform_shop_mutations(): void
    {
        $product = $this->createProduct();
        $user = $this->createUnverifiedCustomer();

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($user)
            ->post(route('shop.rate', [$product->public_id, $product->slug]), ['rating' => 5])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('ratings', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_unsafe_return_url_is_replaced_with_local_shop_path(): void
    {
        $product = $this->createProduct();

        $this->get(route('register', [
            'action' => 'add_to_cart',
            'product_id' => $product->id,
            'quantity' => 1,
            'redirect' => '//evil.example/steal',
        ]))
            ->assertOk()
            ->assertSessionHas(PendingShopAction::SESSION_KEY, function (array $pending): bool {
                return $pending['return_url'] === '/shop';
            });
    }

    public function test_unavailable_product_returns_error_and_clears_pending_action(): void
    {
        $product = $this->createProduct(['stock' => 0]);
        $user = $this->createUnverifiedCustomer();
        $returnUrl = route('shop.show', [$product->public_id, $product->slug], false);

        $this->withSession([
                PendingShopAction::SESSION_KEY => [
                    'action' => 'add_to_cart',
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'return_url' => $returnUrl,
                ],
            ])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect($returnUrl)
            ->assertSessionHas('error', 'Selected product is currently unavailable.')
            ->assertSessionMissing(PendingShopAction::SESSION_KEY);

        $this->assertDatabaseCount('cart_items', 0);
    }

    private function createProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Pending Actions',
            'slug' => 'pending-actions-'.uniqid(),
            'description' => 'Products used by pending action tests.',
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'new_price' => 15000,
            'stock' => 10,
        ], $overrides));
    }

    private function createUnverifiedCustomer(): User
    {
        return User::factory()->unverified()->create([
            'role' => 'customer',
            'phone_number' => '2557'.fake()->unique()->numerify('########'),
        ]);
    }

}
