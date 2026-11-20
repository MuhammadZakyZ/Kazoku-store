<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Dasar semua tes Kazoku Store.
 *
 * PENGAMAN: RefreshDatabase menghapus semua tabel. Tes hanya boleh berjalan di
 * SQLite dalam memori, tidak pernah di MySQL berisi data Anda. Bila bukan,
 * tes dihentikan SEBELUM menyentuh basis data.
 */
abstract class ShopTestCase extends TestCase
{
    use RefreshDatabase;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Tes dihentikan demi keamanan data: koneksi saat ini '{$connection}' ({$database}). ".
                'Di phpunit.xml, aktifkan DB_CONNECTION=sqlite dan DB_DATABASE=:memory:.'
            );
        }
    }

    protected function makeUser(string $name, UserRole $role, bool $verified = true): User
    {
        static $n = 0;
        $n++;

        $user = new User(['name' => $name, 'email' => "user{$n}@contoh.test", 'phone' => '62811111111'.$n, 'password' => 'Secret123']);
        $user->role = $role;
        $user->email_verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    protected function admin(): User
    {
        return $this->makeUser('Admin Uji', UserRole::Admin);
    }

    protected function customer(bool $verified = true): User
    {
        return $this->makeUser('Pelanggan Uji', UserRole::Customer, $verified);
    }

    /** Data toko: harga dasar 100.000; ukuran S +0 dan XL +5.000; area Dada 20.000 dan Punggung 35.000; JNE ke Malang 15.000; DP 50%. */
    protected function shop(): array
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $product = $category->products()->create([
            'name' => 'Kaos Polos', 'description' => null, 'base_price' => 100000, 'image' => null, 'is_active' => true,
        ]);
        $s = $product->options()->create(['name' => 'S', 'extra_price' => 0]);
        $xl = $product->options()->create(['name' => 'XL', 'extra_price' => 5000]);
        $black = $product->colors()->create(['name' => 'Hitam']);
        $chest = $product->printAreas()->create(['name' => 'Dada', 'width_cm' => 10, 'height_cm' => 10, 'price' => 20000]);
        $back = $product->printAreas()->create(['name' => 'Punggung', 'width_cm' => 30, 'height_cm' => 40, 'price' => 35000]);
        $courier = Courier::create(['name' => 'JNE', 'is_active' => true]);
        $rate = $courier->shippingRates()->create(['destination' => 'Malang', 'rate' => 15000]);

        Setting::put('dp_percent', '50');
        Setting::put('free_revision_limit', '2');

        return compact('category', 'product', 's', 'xl', 'black', 'chest', 'back', 'courier', 'rate');
    }

    /** Isian form pemesanan yang sah. $over menimpa atau menambah isian. */
    protected function orderPayload(array $shop, array $over = []): array
    {
        return array_merge([
            'option_id' => $shop['xl']->id,
            'color_id' => $shop['black']->id,
            'quantity' => 3,
            'design_type' => 'punya_desain',
            'area_ids' => [$shop['chest']->id, $shop['back']->id],
            'designs' => [
                $shop['chest']->id => UploadedFile::fake()->create('dada.pdf', 100, 'application/pdf'),
                $shop['back']->id => UploadedFile::fake()->create('punggung.pdf', 100, 'application/pdf'),
            ],
            'delivery_method' => 'kirim',
            'courier_id' => $shop['courier']->id,
            'shipping_rate_id' => $shop['rate']->id,
            'shipping_address' => 'Jl. Contoh No. 1, Malang',
            'payment_method' => 'dp',
        ], $over);
    }

    /** Pesanan langsung lewat model (tanpa form), total Rp 300.000 dan DP Rp 150.000. */
    protected function makeOrder(User $owner, array $shop, array $attrs = []): Order
    {
        $order = new Order(['design_type' => 'punya_desain', 'delivery_method' => 'ambil', 'payment_method' => 'dp']);
        $order->user_id = $owner->id;
        $order->total = 300000;
        $order->dp_amount = 150000;
        $order->save();

        $order->items()->create([
            'product_id' => $shop['product']->id, 'option_name' => 'S', 'color_name' => 'Hitam',
            'quantity' => 1, 'unit_price' => 300000,
        ]);

        foreach ($attrs as $key => $value) {
            $order->{$key} = $value;
        }
        $order->save();

        return $order->fresh();
    }
}
