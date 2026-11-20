<?php

namespace App\Support;

use App\Enums\DeliveryMethod;
use App\Enums\DesignType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Order;

/**
 * Aturan alur status pesanan (Tabel 6 di handoff) dan sinkronisasi status pembayaran.
 */
class OrderWorkflow
{
    /** @return array<int, OrderStatus> status tujuan yang boleh dipilih admin dari status saat ini */
    public static function allowedNext(Order $order): array
    {
        $consult = $order->design_type === DesignType::Konsultasi;
        $pickup = $order->delivery_method === DeliveryMethod::Ambil;

        return match ($order->status) {
            OrderStatus::Baru, OrderStatus::MenungguVerifikasi => $consult
                ? [OrderStatus::Konsultasi, OrderStatus::Diproses]
                : [OrderStatus::Diproses],
            OrderStatus::Konsultasi => [OrderStatus::Diproses],
            OrderStatus::Diproses => $pickup ? [OrderStatus::SiapDiambil] : [OrderStatus::Dikirim],
            OrderStatus::SiapDiambil, OrderStatus::Dikirim => [OrderStatus::Selesai],
            default => [],
        };
    }

    /** Alasan perubahan ditolak, atau null bila boleh. */
    public static function blocker(Order $order, OrderStatus $to): ?string
    {
        if ($to === OrderStatus::Diproses) {
            // Tabel 6: Diproses = pembayaran terverifikasi dan desain siap.
            $paidEnough = $order->payment_method === PaymentMethod::DiAkhir
                || in_array($order->payment_status, [PaymentStatus::Dp, PaymentStatus::Lunas], true);

            if (! $paidEnough) {
                return 'Pembayaran belum terverifikasi.';
            }

            if ($order->design_type === DesignType::Konsultasi && ! self::hasFinalDesign($order)) {
                return 'Desain final belum diunggah.';
            }
        }

        if ($to === OrderStatus::Selesai && $order->payment_status !== PaymentStatus::Lunas) {
            return 'Pesanan belum lunas.';
        }

        return null;
    }

    public static function canCancel(Order $order): bool
    {
        return ! in_array($order->status, [OrderStatus::Selesai, OrderStatus::Dibatalkan], true);
    }

    public static function isClosed(Order $order): bool
    {
        return in_array($order->status, [OrderStatus::Selesai, OrderStatus::Dibatalkan], true);
    }

    public static function hasFinalDesign(Order $order): bool
    {
        return $order->designs()->where('type', 'final')->exists();
    }

    public static function hasPendingPayment(Order $order): bool
    {
        return $order->payments()->where('status', VerificationStatus::Menunggu->value)->exists();
    }

    /** Status pembayaran dihitung dari total pembayaran terverifikasi. */
    public static function syncPaymentStatus(Order $order): void
    {
        $paid = (int) $order->payments()
            ->where('status', VerificationStatus::Terverifikasi->value)
            ->sum('amount');

        $status = match (true) {
            $paid <= 0 => PaymentStatus::BelumBayar,
            $paid >= $order->grand_total => PaymentStatus::Lunas,
            default => PaymentStatus::Dp,
        };

        if ($order->payment_status !== $status) {
            $order->payment_status = $status;
            $order->save();
        }
    }

    /**
     * Setelah pembayaran diverifikasi: sinkronkan status bayar. Bila pesanan masih
     * Menunggu Verifikasi dan tidak ada bukti lain yang menunggu, lanjutkan ke
     * Konsultasi (jalur konsultasi tanpa desain final) atau Diproses.
     */
    public static function afterVerified(Order $order): void
    {
        self::syncPaymentStatus($order);

        if ($order->status === OrderStatus::MenungguVerifikasi && ! self::hasPendingPayment($order)) {
            $order->status = ($order->design_type === DesignType::Konsultasi && ! self::hasFinalDesign($order))
                ? OrderStatus::Konsultasi
                : OrderStatus::Diproses;
            $order->save();
        }
    }

    /** Setelah bukti ditolak: kembali ke Baru bila tidak ada bukti lain yang menunggu. */
    public static function afterRejected(Order $order): void
    {
        self::syncPaymentStatus($order);

        if ($order->status === OrderStatus::MenungguVerifikasi && ! self::hasPendingPayment($order)) {
            $order->status = OrderStatus::Baru;
            $order->save();
        }
    }
}
