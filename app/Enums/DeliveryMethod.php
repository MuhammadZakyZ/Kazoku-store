<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Ambil = 'ambil';
    case Kirim = 'kirim';

    public function label(): string
    {
        return match ($this) {
            self::Ambil => 'Ambil di Toko',
            self::Kirim => 'Kirim',
        };
    }
}
