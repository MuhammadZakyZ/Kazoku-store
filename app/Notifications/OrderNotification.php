<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email notifikasi pesanan (BR-15). Tiap email memuat tautan ke pesanan.
 * Hanya menyimpan data sederhana (teks dan alamat), jadi aman diantrekan.
 */
class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<int, string> $lines */
    public function __construct(
        public string $subject,
        public array $lines,
        public string $actionText,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Halo, '.($notifiable->name ?? 'pelanggan').'!');

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        return $mail->action($this->actionText, $this->url)->salutation('Salam, Kazoku Store');
    }

    // ---------- untuk admin ----------

    public static function newOrder(Order $order): self
    {
        return new self(
            "Pesanan baru {$order->code}",
            [
                "Ada pesanan baru dari {$order->user->name}.",
                "Kode pesanan: {$order->code}",
                'Total tagihan: '.self::rp($order->grand_total).'.',
                'Metode pembayaran: '.$order->payment_method->label().'.',
            ],
            'Buka pesanan',
            route('admin.orders.show', $order),
        );
    }

    public static function proofUploaded(Order $order, Payment $payment): self
    {
        return new self(
            "Bukti pembayaran baru untuk {$order->code}",
            [
                "{$order->user->name} mengunggah bukti {$payment->type->label()} sebesar ".self::rp($payment->amount)." untuk pesanan {$order->code}.",
                'Silakan periksa dan verifikasi atau tolak bukti tersebut.',
            ],
            'Periksa pembayaran',
            route('admin.orders.show', $order),
        );
    }

    // ---------- untuk pelanggan ----------

    public static function paymentVerified(Order $order, Payment $payment): self
    {
        return new self(
            "Pembayaran pesanan {$order->code} diverifikasi",
            [
                "Pembayaran {$payment->type->label()} sebesar ".self::rp($payment->amount)." untuk pesanan {$order->code} sudah diverifikasi.",
                'Status pembayaran: '.$order->payment_status->label().'.',
                'Status pesanan: '.$order->status->label().'.',
            ],
            'Lihat pesanan',
            route('orders.show', $order),
        );
    }

    public static function paymentRejected(Order $order, Payment $payment): self
    {
        return new self(
            "Bukti pembayaran pesanan {$order->code} ditolak",
            [
                "Bukti {$payment->type->label()} sebesar ".self::rp($payment->amount)." untuk pesanan {$order->code} belum dapat kami terima.",
                'Alasan: '.($payment->reject_reason ?: '-'),
                'Silakan unggah ulang bukti pembayaran yang benar lewat halaman pesanan.',
            ],
            'Unggah ulang bukti',
            route('orders.show', $order),
        );
    }

    public static function finalDesign(Order $order): self
    {
        return new self(
            "Desain final pesanan {$order->code} tersedia",
            [
                "Desain final untuk pesanan {$order->code} sudah diunggah oleh admin.",
                'Anda dapat mengunduhnya di halaman pesanan.',
            ],
            'Lihat pesanan',
            route('orders.show', $order),
        );
    }

    public static function revisionUpdated(Order $order): self
    {
        return new self(
            "Revisi pesanan {$order->code} diperbarui",
            [
                "Jumlah revisi: {$order->revision_count} kali.",
                'Biaya revisi: '.self::rp($order->revision_fee).'.',
                'Total tagihan sekarang: '.self::rp($order->grand_total).'.',
            ],
            'Lihat pesanan',
            route('orders.show', $order),
        );
    }

    public static function statusChanged(Order $order): self
    {
        $lines = ["Status pesanan {$order->code} sekarang: {$order->status->label()}."];

        if ($order->tracking_no) {
            $lines[] = "Nomor resi: {$order->tracking_no}.";
        }
        if ($order->status->value === 'siap_diambil') {
            $lines[] = 'Pesanan Anda sudah siap diambil di toko.';
        }

        return new self("Status pesanan {$order->code}: {$order->status->label()}", $lines, 'Lihat pesanan', route('orders.show', $order));
    }

    public static function cancelled(Order $order): self
    {
        return new self(
            "Pesanan {$order->code} dibatalkan",
            [
                "Pesanan {$order->code} dibatalkan oleh admin.",
                'Alasan: '.($order->cancel_reason ?: '-'),
                'Hubungi kami bila ada pertanyaan.',
            ],
            'Lihat pesanan',
            route('orders.show', $order),
        );
    }

    private static function rp(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
