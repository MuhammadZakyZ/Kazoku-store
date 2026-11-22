<?php

namespace App\Enums;

enum AreaSide: string
{
    case Depan = 'depan';
    case Belakang = 'belakang';

    public function label(): string
    {
        return match ($this) {
            self::Depan => 'Depan',
            self::Belakang => 'Belakang',
        };
    }
}
