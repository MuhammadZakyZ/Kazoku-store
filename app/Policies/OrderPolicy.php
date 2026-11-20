<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Otorisasi pesanan. Penolakan berupa 404 (bukan 403) supaya keberadaan
 * pesanan milik orang lain tidak terungkap.
 */
class OrderPolicy
{
    /** Melihat pesanan, nota, dan mengunduh file: pemilik atau admin. */
    public function view(User $user, Order $order): Response
    {
        return $user->isAdmin() || $order->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /** Mengunggah bukti pembayaran: hanya pemilik (admin memakai Tandai lunas). */
    public function uploadPayment(User $user, Order $order): Response
    {
        return $order->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    /** Mengonfirmasi pesanan diterima: hanya pemilik. */
    public function confirmReceived(User $user, Order $order): Response
    {
        return $order->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
