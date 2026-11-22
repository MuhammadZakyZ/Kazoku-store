<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentChannel;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Concerns\ChecksOrderAccess;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderNotification;
use App\Support\Notify;
use App\Support\PaymentPlanner;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PaymentController extends Controller
{
    use ChecksOrderAccess;

    /** Pelanggan mengunggah bukti transfer. Jenis dan nominal ditentukan server. */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);
        Gate::authorize('uploadPayment', $order); // admin tidak mengunggah atas nama pelanggan

        $request->validate(
            ['proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']],
            [],
            ['proof_file' => 'bukti pembayaran'],
        );

        $path = $request->file('proof_file')->store("proofs/{$order->id}", 'local');

        try {
            $result = DB::transaction(function () use ($order, $path) {
                // Kunci baris pesanan supaya dua unggahan bersamaan tidak lolos keduanya.
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $plan = PaymentPlanner::next($locked);

                if ($plan === null) {
                    return null;
                }

                $payment = new Payment([
                    'type' => $plan['type']->value,
                    'method' => PaymentChannel::Transfer->value,
                    'proof_file' => $path,
                ]);
                $payment->order_id = $locked->id;
                $payment->amount = $plan['amount'];
                $payment->status = VerificationStatus::Menunggu;
                $payment->save();

                // Tabel 6: bukti diunggah -> Menunggu Verifikasi (hanya dari status Baru).
                if ($locked->status === OrderStatus::Baru) {
                    $locked->status = OrderStatus::MenungguVerifikasi;
                    $locked->save();
                }

                return [$plan, $payment];
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        if ($result === null) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Saat ini tidak ada pembayaran yang perlu diunggah untuk pesanan ini.');
        }

        [, $payment] = $result;
        Notify::admins(OrderNotification::proofUploaded($order->load('user'), $payment));

        return redirect()->route('orders.show', $order)
            ->with('status', 'Bukti pembayaran terkirim. Admin akan memeriksanya.');
    }
}