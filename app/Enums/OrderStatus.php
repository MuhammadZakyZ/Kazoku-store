<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Baru = 'baru';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Konsultasi = 'konsultasi';
    case Diproses = 'diproses';
    case SiapDiambil = 'siap_diambil';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Konsultasi => 'Konsultasi',
            self::Diproses => 'Diproses',
            self::SiapDiambil => 'Siap Diambil',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }
}
