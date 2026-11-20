<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Support\Whatsapp;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'products' => Product::active()->with('category')->latest()->take(8)->get(),
            'portfolios' => Portfolio::with('category')->latest()->take(8)->get(),
            'testimonials' => Testimonial::published()->latest()->take(3)->get(),
            'whatsapp' => Whatsapp::link(Setting::get('whatsapp_number'), 'Halo Kazoku Store, saya ingin bertanya tentang pemesanan.'),
        ]);
    }
}