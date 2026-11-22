<?php

namespace App\Http\Controllers;

use App\Models\ProductColor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /** Mockup per warna dan sisi (depan atau belakang): disk privat, hanya untuk pengguna yang sudah login. */
    public function mockup(ProductColor $color, string $side = 'depan'): Response
    {
        $path = $color->mockupPath($side);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
