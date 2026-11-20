<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Dp = 'dp';
    case PenuhDiAwal = 'penuh_di_awal';
    case DiAkhir = 'di_akhir';

    public function label(): string
    {
        return match ($this) {
            self::Dp => 'DP',
            self::PenuhDiAwal => 'Bayar Penuh di Awal',
            self::DiAkhir => 'Bayar di Akhir',
        };
    }
}
