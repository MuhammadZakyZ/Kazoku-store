<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Whatsapp;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $number = Setting::get('whatsapp_number');

        return view('contact', [
            'address' => Setting::get('address'),
            'mapsUrl' => Setting::get('maps_url'),
            'openingHours' => Setting::get('opening_hours'),
            'whatsappNumber' => $number,
            'whatsapp' => Whatsapp::link($number, 'Halo Kazoku Store, saya ingin bertanya.'),
            'dpPercent' => Setting::get('dp_percent'),
            'freeRevisionLimit' => Setting::get('free_revision_limit'),
        ]);
    }
}