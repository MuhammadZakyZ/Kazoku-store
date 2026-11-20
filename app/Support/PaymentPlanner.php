<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\VerificationStatus;
use App\Models\Order;

/**
 * Menentukan pembayaran berikutnya yang boleh diunggah pelanggan.
 * Jenis dan nominal ditentukan server, bukan dari isian pelanggan.
 */
class PaymentPlanner
{
    /** @return array{type: PaymentType, amount: int}|null */
    public static function next(Order $order): ?array
    {
        if (in_array($order->status, [OrderStatus::Dibatalkan, OrderStatus::Selesai], true)) {
            return null;
        }

        // BR-08: pembayaran di akhir tanpa bukti; admin yang menandai lunas.
        if ($order->payment_method === PaymentMethod::DiAkhir) {
            return null;
        }

        // Tunggu admin memeriksa bukti sebelumnya.
        if ($order->payments()->where('status', VerificationStatus::Menunggu->value)->exists()) {
            return null;
        }

        $paid = (int) $order->payments()
            ->where('status', VerificationStatus::Terverifikasi->value)
            ->sum('amount');

        $remaining = max(0, $order->grand_total - $paid);
        if ($remaining === 0) {
            return null;
        }

        if ($paid > 0) {
            return ['type' => PaymentType::Pelunasan, 'amount' => $remaining];
        }

        if ($order->payment_method === PaymentMethod::Dp && $order->dp_amount > 0) {
            return ['type' => PaymentType::Dp, 'amount' => min($order->dp_amount, $remaining)];
        }

        return ['type' => PaymentType::Penuh, 'amount' => $remaining];
    }
}
