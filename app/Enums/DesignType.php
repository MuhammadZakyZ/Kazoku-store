<?php

namespace App\Enums;

enum DesignType: string
{
    case PunyaDesain = 'punya_desain';
    case Konsultasi = 'konsultasi';

    public function label(): string
    {
        return match ($this) {
            self::PunyaDesain => 'Sudah Punya Desain',
            self::Konsultasi => 'Konsultasi',
        };
    }
}
