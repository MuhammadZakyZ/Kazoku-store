<?php

namespace App\Http\Controllers;

use App\Models\ProductColor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /** Mockup per warna: disk privat, hanya untuk pengguna yang sudah login. */
    public function mockup(ProductColor $color): Response
    {
        abort_unless($color->mockup_image && Storage::disk('local')->exists($color->mockup_image), 404);

        return Storage::disk('local')->response($color->mockup_image);
    }
}
