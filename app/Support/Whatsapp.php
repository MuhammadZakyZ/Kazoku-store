<?php

namespace App\Support;

class Whatsapp
{
    /** Tautan wa.me berisi pesan awal; null bila nomor belum diisi di Pengaturan. */
    public static function link(?string $number, string $message = ''): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.($message !== '' ? '?text='.rawurlencode($message) : '');
    }
}
