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
        $portfolios = Portfolio::with('category')->latest()->take(3)->get();

        // Foto hero: letakkan file public/images/hero-beranda.(jpg|jpeg|png|webp) untuk memakainya;
        // bila tidak ada, dipakai foto karya portofolio terbaru.
        $heroFile = collect(['jpg', 'jpeg', 'png', 'webp'])
            ->map(fn ($ext) => 'images/hero-beranda.'.$ext)
            ->first(fn ($path) => file_exists(public_path($path)));
        $heroImage = $heroFile
            ? asset($heroFile)
            : ($portfolios->first() ? asset('storage/'.$portfolios->first()->image) : null);

        return view('home', [
            'products' => Product::active()
                ->with(['category', 'options:id,product_id,name', 'colors:id,product_id'])
                ->latest()->take(4)->get(),
            'portfolios' => $portfolios,
            'testimonials' => Testimonial::published()->latest()->take(3)->get(),
            'heroImage' => $heroImage,
            'whatsapp' => Whatsapp::link(Setting::get('whatsapp_number'), 'Halo Kazoku Store, saya ingin bertanya tentang pemesanan.'),
        ]);
    }
}