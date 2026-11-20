<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Portfolio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('admin.portfolios.index', [
            'portfolios' => Portfolio::with('category')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.portfolios.create', [
            'portfolio' => new Portfolio,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(imageRequired: true));
        $data['image'] = $request->file('image')->store('portfolios', 'public');

        Portfolio::create($data);

        return redirect()->route('admin.portfolios.index')->with('status', 'Portofolio ditambahkan.');
    }

    public function edit(Portfolio $portfolio): View
    {
        return view('admin.portfolios.edit', [
            'portfolio' => $portfolio,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $data = $request->validate($this->rules(imageRequired: false));

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($portfolio->image);
            $data['image'] = $request->file('image')->store('portfolios', 'public');
        } else {
            unset($data['image']);
        }

        $portfolio->update($data);

        return redirect()->route('admin.portfolios.index')->with('status', 'Portofolio diperbarui.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        Storage::disk('public')->delete($portfolio->image);
        $portfolio->delete();

        return back()->with('status', 'Portofolio dihapus.');
    }

    private function rules(bool $imageRequired): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:150'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
