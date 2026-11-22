<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Whatsapp;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data footer (WhatsApp, email, peta) diambil dari Pengaturan admin.
        View::composer('partials.footer', function ($view) {
            $address = trim((string) Setting::get('address'));

            $view->with([
                'footerWhatsapp' => Whatsapp::link(Setting::get('whatsapp_number'), 'Halo Kazoku Store, saya ingin bertanya.'),
                'footerEmail' => filled($email = Setting::get('business_email')) ? $email : null,
                // Utamakan tautan peta yang diisi admin; bila kosong, cari alamat di Google Maps.
                'footerMap' => filled($mapsUrl = Setting::get('maps_url'))
                    ? $mapsUrl
                    : ($address !== ''
                        ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address)
                        : null),
            ]);
        });
    }
}