<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('testimonials.index', [
            // BR-09: hanya yang dipublikasikan.
            'testimonials' => Testimonial::published()->latest()->paginate(12),
        ]);
    }
}
