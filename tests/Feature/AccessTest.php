<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\ShopTestCase;

class AccessTest extends ShopTestCase
{
    public function test_guest_is_sent_to_login(): void
    {
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $this->get(route('orders.show', $order))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_owner_and_admin_can_open_order_but_other_customer_gets_404(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $admin = $this->admin();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($admin)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($other)->get(route('orders.show', $order))->assertNotFound();
        $this->actingAs($other)->get(route('orders.invoice', $order))->assertNotFound();
    }

    public function test_orders_list_only_shows_own_orders(): void
    {
        $shop = $this->shop();
        $owner = $this->customer();
        $other = $this->customer();
        $mine = $this->makeOrder($owner, $shop);
        $theirs = $this->makeOrder($other, $shop);

        $this->actingAs($owner)->get(route('orders.index'))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertDontSee($theirs->code);
    }

    public function test_customer_cannot_use_admin_area(): void
    {
        $customer = $this->customer();
        $order = $this->makeOrder($customer, $this->shop());

        $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.orders.show', $order))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.status', $order), ['status' => 'diproses'])->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.cancel', $order), ['cancel_reason' => 'iseng saja'])->assertForbidden();
    }

    public function test_design_file_download_is_limited_to_owner_and_admin(): void
    {
        Storage::fake('local');
        $shop = $this->shop();
        $owner = $this->customer();
        $other = $this->customer();
        $admin = $this->admin();
        $order = $this->makeOrder($owner, $shop);

        Storage::disk('local')->put('designs/1/dada.pdf', 'isi');
        $design = $order->designs()->create([
            'print_area_id' => $shop['chest']->id, 'area_name' => 'Dada', 'print_size' => '10×10 cm',
            'area_price' => 20000, 'file' => 'designs/1/dada.pdf', 'type' => 'pelanggan',
        ]);

        $this->actingAs($owner)->get(route('files.design', [$order, $design]))->assertOk();
        $this->actingAs($admin)->get(route('files.design', [$order, $design]))->assertOk();
        $this->actingAs($other)->get(route('files.design', [$order, $design]))->assertNotFound();
    }
}
