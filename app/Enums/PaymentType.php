<?php

namespace App\Enums;

enum PaymentType: string
{
    case Dp = 'dp';
    case Pelunasan = 'pelunasan';
    case Penuh = 'penuh';

    public function label(): string
    {
        return match ($this) {
            self::Dp => 'DP',
            self::Pelunasan => 'Pelunasan',
            self::Penuh => 'Bayar Penuh',
        };
    }
}
