<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Nilai di bawah adalah PLACEHOLDER. Persen DP dan batas revisi gratis
     * harus ditentukan oleh mitra; ubah lewat panel admin (UC-33) setelah tersedia.
     * Kunci yang sudah ada tidak ditimpa.
     */
    public function run(): void
    {
        $defaults = [
            'whatsapp_number' => '',      // format 628xxxxxxxxxx
            'dp_percent' => '50',         // placeholder, tunggu mitra
            'free_revision_limit' => '3', // placeholder, tunggu mitra
            'address' => '',
            'opening_hours' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
