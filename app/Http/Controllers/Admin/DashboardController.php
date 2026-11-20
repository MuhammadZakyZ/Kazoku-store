<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'Pesanan baru' => Order::where('status', OrderStatus::Baru->value)->count(),
                'Bukti menunggu verifikasi' => Payment::where('status', VerificationStatus::Menunggu->value)->count(),
                'Produk' => Product::count(),
                'Kategori' => Category::count(),
                'Portofolio' => Portfolio::count(),
                'Testimoni tayang' => Testimonial::published()->count(),
                'Ekspedisi' => Courier::count(),
            ],
        ]);
    }
}