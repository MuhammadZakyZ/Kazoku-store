<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case BelumBayar = 'belum_bayar';
    case Dp = 'dp';
    case Lunas = 'lunas';

    public function label(): string
    {
        return match ($this) {
            self::BelumBayar => 'Belum Bayar',
            self::Dp => 'DP',
            self::Lunas => 'Lunas',
        };
    }
}
