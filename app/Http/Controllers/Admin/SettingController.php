<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = ['whatsapp_number', 'dp_percent', 'free_revision_limit', 'address', 'maps_url', 'opening_hours', 'payment_instructions'];

    public function edit(): View
    {
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = Setting::get($key, '');
        }

        return view('admin.settings.edit', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (filled($request->input('whatsapp_number'))) {
            $request->merge([
                'whatsapp_number' => RegisterController::normalizePhone((string) $request->input('whatsapp_number')),
            ]);
        }

        $data = $request->validate([
            'whatsapp_number' => ['nullable', 'regex:/^628\d{8,12}$/'],
            'dp_percent' => ['required', 'integer', 'between:0,100'],
            'free_revision_limit' => ['required', 'integer', 'min:0', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'maps_url' => ['nullable', 'url:http,https', 'max:500'],
            'opening_hours' => ['nullable', 'string', 'max:500'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach (self::KEYS as $key) {
            Setting::put($key, $data[$key] ?? '');
        }

        return back()->with('status', 'Pengaturan disimpan.');
    }
}