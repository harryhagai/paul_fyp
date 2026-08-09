<?php

namespace Tests\Feature;

use App\Http\Controllers\seller\RobotArmController;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RobotCommand;
use App\Models\User;
use App\Services\RobotArmCommandService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class RobotArmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('robot.enabled', true);
        config()->set('robot.base_url', 'http://192.168.1.50');
        config()->set('robot.command_endpoint', '/robot/command');
        config()->set('robot.locations', range(1, 5));
    }

    public function test_pick_sends_the_documented_payload_and_records_acceptance(): void
    {
        $order = $this->createOrder([3]);

        Http::fake([
            'http://192.168.1.50/robot/command' => Http::response([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 3,
            ]),
        ]);

        $command = app(RobotArmCommandService::class)->dispatchPickForOrder($order);

        $this->assertSame(RobotCommand::STATUS_ACCEPTED, $command->status);
        $this->assertSame(3, $command->location);
        $this->assertNull($command->error);

        Http::assertSent(function (Request $request) use ($order): bool {
            return $request->url() === 'http://192.168.1.50/robot/command'
                && $request->method() === 'POST'
                && $request->data() === [
                    'command' => 'PICK',
                    'order_id' => $order->order_number,
                    'location' => 3,
                ];
        });
    }

    public function test_invalid_robot_json_is_recorded_as_an_error_instead_of_accepted(): void
    {
        $order = $this->createOrder([2]);
        Http::fake(['*' => Http::response('not-json', 200)]);

        $command = app(RobotArmCommandService::class)->dispatchPickForOrder($order);

        $this->assertSame(RobotCommand::STATUS_ERROR, $command->status);
        $this->assertSame('INVALID_ROBOT_RESPONSE', $command->error);
    }

    public function test_completed_status_updates_a_confirmed_order(): void
    {
        $order = $this->createOrder([4]);
        Http::fakeSequence()
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 4,
            ])
            ->push([
                'status' => 'COMPLETED',
                'order_id' => $order->order_number,
                'location' => 4,
            ]);

        $service = app(RobotArmCommandService::class);
        $command = $service->dispatchPickForOrder($order);
        $status = $service->pollStatus();

        $this->assertTrue($status['ok']);
        $this->assertSame(RobotCommand::STATUS_COMPLETED, $command->fresh()->status);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_failed_status_poll_does_not_destroy_an_active_command(): void
    {
        $order = $this->createOrder([1]);
        Http::fakeSequence()
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 1,
            ])
            ->push(['message' => 'temporary failure'], 503);

        $service = app(RobotArmCommandService::class);
        $command = $service->dispatchPickForOrder($order);
        $status = $service->pollStatus();

        $this->assertFalse($status['ok']);
        $this->assertSame(RobotCommand::STATUS_ACCEPTED, $command->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_idle_status_does_not_overwrite_an_active_command(): void
    {
        $order = $this->createOrder([5]);
        Http::fakeSequence()
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 5,
            ])
            ->push(['status' => 'IDLE']);

        $service = app(RobotArmCommandService::class);
        $command = $service->dispatchPickForOrder($order);
        $service->pollStatus();

        $this->assertSame(RobotCommand::STATUS_ACCEPTED, $command->fresh()->status);
    }

    public function test_out_of_order_progress_response_cannot_regress_a_command(): void
    {
        $order = $this->createOrder([5]);
        Http::fakeSequence()
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 5,
            ])
            ->push([
                'status' => 'PLACING',
                'order_id' => $order->order_number,
            ])
            ->push([
                'status' => 'MOVING',
                'order_id' => $order->order_number,
            ]);

        $service = app(RobotArmCommandService::class);
        $command = $service->dispatchPickForOrder($order);
        $service->pollStatus();
        $service->pollStatus();

        $this->assertSame(RobotCommand::STATUS_PLACING, $command->fresh()->status);
    }

    public function test_duplicate_confirmation_only_queues_an_order_once(): void
    {
        $order = $this->createOrder([2]);
        Http::fake();

        $service = app(RobotArmCommandService::class);
        $first = $service->dispatchPickForOrderIfNeeded($order);
        $second = $service->dispatchPickForOrderIfNeeded($order);

        $this->assertSame(RobotCommand::STATUS_QUEUED, $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('robot_arm_commands', 1);
        Http::assertNothingSent();
    }

    public function test_three_orders_are_processed_in_fifo_order(): void
    {
        $orders = collect([
            $this->createOrder([1]),
            $this->createOrder([2]),
            $this->createOrder([3]),
        ]);

        Http::fakeSequence()
            ->push(['status' => 'ACCEPTED', 'order_id' => $orders[0]->order_number, 'location' => 1])
            ->push(['status' => 'COMPLETED', 'order_id' => $orders[0]->order_number, 'location' => 1])
            ->push(['status' => 'ACCEPTED', 'order_id' => $orders[1]->order_number, 'location' => 2])
            ->push(['status' => 'COMPLETED', 'order_id' => $orders[1]->order_number, 'location' => 2])
            ->push(['status' => 'ACCEPTED', 'order_id' => $orders[2]->order_number, 'location' => 3])
            ->push(['status' => 'COMPLETED', 'order_id' => $orders[2]->order_number, 'location' => 3]);

        $service = app(RobotArmCommandService::class);
        $orders->each(fn (Order $order) => $service->dispatchPickForOrderIfNeeded($order));

        $this->assertSame(3, RobotCommand::where('status', RobotCommand::STATUS_QUEUED)->count());
        Http::assertNothingSent();

        $service->processQueue();
        $service->processQueue();
        $service->processQueue();
        $service->processQueue();

        $pickOrder = Http::recorded()
            ->map(fn (array $entry) => $entry[0]->data())
            ->filter(fn (array $payload) => ($payload['command'] ?? null) === 'PICK')
            ->pluck('order_id')
            ->values()
            ->all();

        $this->assertSame($orders->pluck('order_number')->all(), $pickOrder);
        $this->assertSame(3, RobotCommand::where('status', RobotCommand::STATUS_COMPLETED)->count());
        $orders->each(fn (Order $order) => $this->assertSame('completed', $order->fresh()->status));
    }

    public function test_multi_item_order_is_split_into_serial_pick_cycles(): void
    {
        $order = $this->createOrder([1, 2]);
        $order->orderItems->first()->update(['quantity' => 2]);
        $order = $order->fresh(['orderItems.product']);

        Http::fakeSequence()
            ->push(['status' => 'ACCEPTED', 'order_id' => $order->order_number, 'location' => 1])
            ->push(['status' => 'COMPLETED', 'order_id' => $order->order_number, 'location' => 1])
            ->push(['status' => 'ACCEPTED', 'order_id' => $order->order_number, 'location' => 1])
            ->push(['status' => 'COMPLETED', 'order_id' => $order->order_number, 'location' => 1])
            ->push(['status' => 'ACCEPTED', 'order_id' => $order->order_number, 'location' => 2])
            ->push(['status' => 'COMPLETED', 'order_id' => $order->order_number, 'location' => 2]);

        $service = app(RobotArmCommandService::class);
        $service->dispatchPickForOrderIfNeeded($order);

        $commands = RobotCommand::where('order_id', $order->id)->oldest('sequence')->get();
        $this->assertSame([1, 1, 2], $commands->pluck('location')->all());
        $this->assertSame([1, 2, 3], $commands->pluck('sequence')->all());
        $this->assertSame([3, 3, 3], $commands->pluck('total')->all());
        Http::assertNothingSent();

        $service->processQueue();
        $service->processQueue();
        $this->assertSame('confirmed', $order->fresh()->status);

        $service->processQueue();
        $this->assertSame('confirmed', $order->fresh()->status);

        $service->processQueue();
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame(3, RobotCommand::where('order_id', $order->id)->where('status', RobotCommand::STATUS_COMPLETED)->count());
    }

    public function test_robot_busy_keeps_a_queued_command_available_for_retry(): void
    {
        $order = $this->createOrder([2]);
        Http::fakeSequence()
            ->push([
                'status' => 'ERROR',
                'order_id' => $order->order_number,
                'location' => 2,
                'error' => 'ROBOT_BUSY',
            ])
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 2,
            ]);

        $service = app(RobotArmCommandService::class);
        $command = $service->dispatchPickForOrderIfNeeded($order);

        $service->processQueue();
        $this->assertSame(RobotCommand::STATUS_QUEUED, $command->fresh()->status);

        $service->processQueue();
        $this->assertSame(RobotCommand::STATUS_ACCEPTED, $command->fresh()->status);
        Http::assertSentCount(2);
    }

    public function test_monitor_reports_offline_when_queue_is_empty_and_esp32_is_unreachable(): void
    {
        Http::fake(['*' => Http::response(['message' => 'unreachable'], 503)]);

        $response = app(RobotArmController::class)
            ->status(app(RobotArmCommandService::class))
            ->getData(true);

        $this->assertFalse($response['robot']['online']);
        $this->assertSame(RobotCommand::STATUS_ERROR, $response['robot']['status']);
        Http::assertSentCount(1);
    }

    public function test_automatic_pick_rejects_an_ambiguous_multi_location_order(): void
    {
        $order = $this->createOrder([1, 2]);
        Http::fake();

        $command = app(RobotArmCommandService::class)->dispatchPickForOrder($order);

        $this->assertSame(RobotCommand::STATUS_ERROR, $command->status);
        $this->assertSame('MULTIPLE_PICKS_NOT_SUPPORTED', $command->error);
        Http::assertNothingSent();
    }

    public function test_automatic_pick_rejects_a_quantity_that_requires_multiple_pick_cycles(): void
    {
        $order = $this->createOrder([1]);
        $order->orderItems->first()->update(['quantity' => 2]);
        Http::fake();

        $command = app(RobotArmCommandService::class)
            ->dispatchPickForOrder($order->fresh(['orderItems.product']));

        $this->assertSame(RobotCommand::STATUS_ERROR, $command->status);
        $this->assertSame('MULTIPLE_PICKS_NOT_SUPPORTED', $command->error);
        Http::assertNothingSent();
    }

    public function test_robot_completion_cannot_complete_a_pending_order(): void
    {
        $order = $this->createOrder([1], 'pending');
        Http::fakeSequence()
            ->push([
                'status' => 'ACCEPTED',
                'order_id' => $order->order_number,
                'location' => 1,
            ])
            ->push([
                'status' => 'COMPLETED',
                'order_id' => $order->order_number,
                'location' => 1,
            ]);

        $service = app(RobotArmCommandService::class);
        $service->dispatchPickForOrder($order);
        $service->pollStatus();

        $this->assertSame('pending', $order->fresh()->status);
    }

    private function createOrder(array $locations, string $status = 'confirmed'): Order
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Robot parcels '.Str::random(8),
            'slug' => 'robot-parcels-'.Str::lower(Str::random(8)),
        ]);

        $order = Order::create([
            'order_number' => 'ORD'.Str::upper(Str::random(8)),
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'TZS',
            'ordered_at' => now(),
        ]);

        foreach ($locations as $index => $location) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Parcel '.Str::random(8),
                'slug' => 'parcel-'.Str::lower(Str::random(8)),
                'new_price' => 1000,
                'stock' => 10,
                'robot_location' => $location,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => 'SKU-'.$index,
                'quantity' => 1,
                'unit_price' => 1000,
                'total_price' => 1000,
            ]);
        }

        return $order->fresh(['orderItems.product']);
    }
}
