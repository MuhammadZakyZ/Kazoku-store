<?php

namespace App\Enums;

enum DesignFileType: string
{
    case Pelanggan = 'pelanggan';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Pelanggan => 'Desain Pelanggan',
            self::Final => 'Desain Final',
        };
    }
}
