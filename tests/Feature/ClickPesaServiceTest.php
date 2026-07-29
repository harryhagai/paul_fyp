<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\ClickPesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickPesaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ussd_push_sends_alphanumeric_order_reference(): void
    {
        config([
            'clickpesa.base_url' => 'https://clickpesa.test/third-parties',
            'clickpesa.client_id' => 'client-id',
            'clickpesa.api_key' => 'api-key',
            'clickpesa.use_checksum' => false,
            'clickpesa.verify_ssl' => false,
        ]);

        Http::fake([
            'https://clickpesa.test/third-parties/generate-token' => Http::response([
                'success' => true,
                'token' => 'token-value',
            ]),
            'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request' => Http::response([
                'id' => 'payment-id',
                'status' => 'PROCESSING',
            ]),
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'order_number' => 'ORD-2026-AZUX5F68',
            'status' => 'pending',
            'subtotal' => 67000,
            'total_amount' => 67000,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255765384905',
            'ordered_at' => now(),
        ]);

        app(ClickPesaService::class)->initiateUssdPush($order);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request'
                && $request['orderReference'] === 'ORD2026AZUX5F68'
                && $request['phoneNumber'] === '255765384905'
                && $request->hasHeader('Authorization', 'Bearer token-value');
        });
    }

    public function test_ussd_push_can_send_custom_retry_order_reference(): void
    {
        config([
            'clickpesa.base_url' => 'https://clickpesa.test/third-parties',
            'clickpesa.client_id' => 'client-id',
            'clickpesa.api_key' => 'api-key',
            'clickpesa.use_checksum' => false,
            'clickpesa.verify_ssl' => false,
        ]);

        Http::fake([
            'https://clickpesa.test/third-parties/generate-token' => Http::response([
                'success' => true,
                'token' => 'token-value',
            ]),
            'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request' => Http::response([
                'id' => 'payment-id',
                'status' => 'PROCESSING',
            ]),
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'order_number' => 'ORD-2026-AZUX5F68',
            'status' => 'pending',
            'subtotal' => 67000,
            'total_amount' => 67000,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255765384905',
            'ordered_at' => now(),
        ]);

        app(ClickPesaService::class)->initiateUssdPush($order, 'ORD2026AZUX5F68RAB12');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request'
                && $request['orderReference'] === 'ORD2026AZUX5F68RAB12';
        });
    }

    public function test_webhook_matches_sanitized_clickpesa_order_reference(): void
    {
        $order = Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'order_number' => 'ORD-2026-AZUX5F68',
            'status' => 'pending',
            'subtotal' => 67000,
            'total_amount' => 67000,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255765384905',
            'ordered_at' => now(),
        ]);

        $this->postJson(route('clickpesa.callback'), [
            'event' => 'PAYMENT FAILED',
            'data' => [
                'orderReference' => 'ORD2026AZUX5F68',
                'status' => 'FAILED',
                'message' => 'Payment cancelled',
            ],
        ])->assertOk();

        $order->refresh();

        $this->assertSame('failed', $order->payment_status);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Payment cancelled', $order->payment_message);
    }

    public function test_webhook_matches_retry_order_reference_suffix(): void
    {
        $order = Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'order_number' => 'ORD-2026-AZUX5F68',
            'status' => 'pending',
            'subtotal' => 67000,
            'total_amount' => 67000,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255765384905',
            'ordered_at' => now(),
        ]);

        $this->postJson(route('clickpesa.callback'), [
            'event' => 'PAYMENT_SUCCESS',
            'data' => [
                'orderReference' => 'ORD2026AZUX5F68RAB12',
                'status' => 'SUCCESS',
                'message' => 'Payment received',
            ],
        ])->assertOk();

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
    }

    public function test_customer_pay_now_uses_clickpesa_reference_within_limit(): void
    {
        config([
            'clickpesa.base_url' => 'https://clickpesa.test/third-parties',
            'clickpesa.client_id' => 'client-id',
            'clickpesa.api_key' => 'api-key',
            'clickpesa.use_checksum' => false,
            'clickpesa.verify_ssl' => false,
        ]);

        Http::fake([
            'https://clickpesa.test/third-parties/generate-token' => Http::response([
                'success' => true,
                'token' => 'token-value',
            ]),
            'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request' => Http::response([
                'id' => 'payment-id',
                'status' => 'PROCESSING',
            ]),
        ]);

        $user = User::factory()->create([
            'role' => 'customer',
            'phone_number' => '+255765384905',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-2026-AZUX5F68',
            'status' => 'pending',
            'subtotal' => 67000,
            'total_amount' => 67000,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255765384905',
            'payment_provider' => 'clickpesa',
            'payment_status' => 'failed',
            'ordered_at' => now(),
        ]);

        $reference = null;

        $this->actingAs($user)
            ->postJson(route('customer.orders.pay', $order))
            ->assertOk()
            ->assertJson(['success' => true]);

        Http::assertSent(function ($request) use (&$reference): bool {
            if ($request->url() !== 'https://clickpesa.test/third-parties/payments/initiate-ussd-push-request') {
                return false;
            }

            $reference = $request['orderReference'];

            return strlen($reference) <= 20
                && str_starts_with($reference, 'ORD2026AZUX5F68R');
        });

        $order->refresh();

        $this->assertSame($reference, $order->clickpesa_payload['orderReference']);
        $this->assertSame('processing', $order->payment_status);
    }

    public function test_callback_can_confirm_order_by_clickpesa_payment_id(): void
    {
        $order = Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'order_number' => 'ORD-2026-DVQGSBKI',
            'status' => 'pending',
            'subtotal' => 500,
            'total_amount' => 500,
            'currency' => 'TZS',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+255688704526',
            'payment_provider' => 'clickpesa',
            'payment_status' => 'processing',
            'clickpesa_payment_id' => 'LCPCAJWA195JFE',
            'stock_deducted_at' => now(),
            'ordered_at' => now(),
        ]);

        $this->getJson(route('clickpesa.callback', [
            'paymentId' => 'LCPCAJWA195JFE',
            'status' => 'PAID',
            'message' => 'Payment received',
            'paymentReference' => 'CP123',
        ]))->assertOk();

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('CP123', $order->clickpesa_payment_reference);
        $this->assertNotNull($order->paid_at);
    }
}
