<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Setting;
use App\Support\Whatsapp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'kategori' => ['nullable', 'exists:categories,id'],
            ],
            [],
            ['q' => 'kata kunci', 'kategori' => 'kategori'],
        );

        $portfolios = Portfolio::with('category')
            ->filter([
                'keyword' => $input['q'] ?? null,
                'category_id' => $input['kategori'] ?? null,
            ])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('portfolios.index', [
            'portfolios' => $portfolios,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $input,
            'whatsapp' => Whatsapp::link(Setting::get('whatsapp_number'), 'Halo Kazoku Store, saya ingin bertanya tentang hasil sablon.'),
        ]);
    }
}