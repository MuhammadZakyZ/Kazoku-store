<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;
use Closure;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * Pengirim notifikasi yang tidak pernah menggagalkan proses utama: bila email
 * bermasalah (SMTP salah, antrean mati), kesalahan hanya dicatat di log.
 */
class Notify
{
    public static function admins(Notification $notification): void
    {
        self::safely(fn () => NotificationFacade::send(User::admins()->get(), $notification));
    }

    public static function customer(Order $order, Notification $notification): void
    {
        self::safely(fn () => $order->user->notify($notification));
    }

    private static function safely(Closure $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
