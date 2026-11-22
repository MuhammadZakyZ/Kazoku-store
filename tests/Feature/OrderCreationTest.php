<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Notifications\OrderNotification;
use App\Support\AreaRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\ShopTestCase;

class OrderCreationTest extends ShopTestCase
{
    public function test_order_form_separates_front_and_back_areas_and_uses_the_back_mockup_route(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();
        $shop['back']->update(['side' => 'belakang']);
        $shop['black']->update([
            'mockup_image' => 'mockups/front.png',
            'mockup_back' => 'mockups/back.png',
        ]);
        Storage::disk('local')->put('mockups/front.png', 'front');
        Storage::disk('local')->put('mockups/back.png', 'back');

        $this->actingAs($customer)
            ->get(route('orders.create', $shop['product']))
            ->assertOk()
            ->assertSee('Area depan')
            ->assertSee('Area belakang')
            ->assertSee(str_replace('/', '\\/', route('media.mockup', [$shop['black'], 'belakang'])), false);

        $this->get(route('products.show', $shop['product']))
            ->assertOk()
            ->assertSee('Area Depan')
            ->assertSee('Area Belakang');
    }

    public function test_customer_cannot_submit_conflicting_print_areas(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();
        AreaRules::setConflicts($shop['chest'], [$shop['back']->id]);

        $this->actingAs($customer)->post(
            route('orders.store', $shop['product']),
            $this->orderPayload($shop)
        )->assertSessionHasErrors('area_ids');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_server_recalculates_totals_and_ignores_forged_fields(): void
    {
        Storage::fake('local');
        Notification::fake();
        $admin = $this->admin();
        $customer = $this->customer();
        $shop = $this->shop();

        $response = $this->actingAs($customer)->post(
            route('orders.store', $shop['product']),
            $this->orderPayload($shop, ['total' => 1, 'dp_amount' => 1, 'status' => 'selesai', 'user_id' => 999])
        );

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        // (100.000 + 5.000 + 20.000 + 35.000) x 3 + ongkir 15.000
        $this->assertSame(495000, $order->total);
        $this->assertSame(247500, $order->dp_amount);
        $this->assertSame(15000, $order->shipping_cost);
        $this->assertSame('baru', $order->status->value);
        $this->assertSame('belum_bayar', $order->payment_status->value);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertMatchesRegularExpression('/^KZK-\d{6}-[A-Z2-9]{4}$/', $order->code);

        $item = $order->items()->firstOrFail();
        $this->assertSame(160000, $item->unit_price);
        $this->assertSame('XL', $item->option_name);
        $this->assertSame('Hitam', $item->color_name);

        $this->assertCount(2, $order->designs);
        foreach ($order->designs as $design) {
            Storage::disk('local')->assertExists($design->file);
        }

        Notification::assertSentTo($admin, OrderNotification::class);
    }

    public function test_pickup_has_no_shipping_cost(): void
    {
        Storage::fake('local');
        Notification::fake();
        $customer = $this->customer();
        $shop = $this->shop();

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop, [
            'delivery_method' => 'ambil', 'courier_id' => null, 'shipping_rate_id' => null, 'shipping_address' => null,
        ]))->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame(0, $order->shipping_cost);
        $this->assertSame(480000, $order->total);
    }

    public function test_dp_amount_is_zero_for_other_payment_methods(): void
    {
        Storage::fake('local');
        Notification::fake();
        $customer = $this->customer();
        $shop = $this->shop();

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop, [
            'payment_method' => 'penuh_di_awal',
        ]));

        $this->assertSame(0, Order::firstOrFail()->dp_amount);
    }

    public function test_consultation_order_has_no_area_cost_or_files(): void
    {
        Storage::fake('local');
        Notification::fake();
        $customer = $this->customer();
        $shop = $this->shop();

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop, [
            'design_type' => 'konsultasi', 'area_ids' => [], 'designs' => [],
        ]))->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        // (100.000 + 5.000) x 3 + 15.000
        $this->assertSame(330000, $order->total);
        $this->assertCount(0, $order->designs);
    }

    public function test_unverified_customer_cannot_order(): void
    {
        $customer = $this->customer(false);
        $shop = $this->shop();

        $this->actingAs($customer)->get(route('orders.create', $shop['product']))
            ->assertRedirect(route('verification.notice'));
        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop))
            ->assertRedirect(route('verification.notice'));

        $this->assertSame(0, Order::count());
    }

    public function test_every_selected_area_needs_a_file(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();

        $payload = $this->orderPayload($shop);
        unset($payload['designs'][$shop['back']->id]);

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $payload)
            ->assertSessionHasErrors('designs.'.$shop['back']->id);

        $this->assertSame(0, Order::count());
    }

    public function test_disallowed_file_type_is_rejected(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();

        $payload = $this->orderPayload($shop);
        $payload['designs'][$shop['chest']->id] = UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload');

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $payload)
            ->assertSessionHasErrors('designs.'.$shop['chest']->id);

        $this->assertSame(0, Order::count());
    }

    public function test_shipping_requires_destination_and_address(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop, [
            'courier_id' => null, 'shipping_rate_id' => null, 'shipping_address' => null,
        ]))->assertSessionHasErrors(['courier_id', 'shipping_rate_id', 'shipping_address']);
    }

    public function test_same_area_cannot_be_chosen_twice(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $shop = $this->shop();

        $this->actingAs($customer)->post(route('orders.store', $shop['product']), $this->orderPayload($shop, [
            'area_ids' => [$shop['chest']->id, $shop['chest']->id],
        ]))->assertSessionHasErrors();

        $this->assertSame(0, Order::count());
    }
}
