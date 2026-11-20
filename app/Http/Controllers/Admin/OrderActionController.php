<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DesignFileType;
use App\Enums\OrderStatus;
use App\Enums\PaymentChannel;
use App\Enums\PaymentType;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Design;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PrintArea;
use App\Notifications\OrderNotification;
use App\Support\Notify;
use App\Support\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Aksi admin pada satu pesanan. Setiap aksi mengunci baris pesanan di dalam
 * transaksi supaya dua admin tidak saling menimpa.
 */
class OrderActionController extends Controller
{
    // ---------- pembayaran ----------

    public function verifyPayment(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        abort_unless($payment->order_id === $order->id, 404);

        DB::transaction(function () use ($request, $order, $payment) {
            $locked = $this->lock($order);
            $fresh = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== VerificationStatus::Menunggu) {
                $this->fail('payment', 'Bukti ini sudah diperiksa sebelumnya.');
            }

            $fresh->status = VerificationStatus::Terverifikasi;
            $fresh->reject_reason = null;
            $fresh->verified_by = $request->user()->id;
            $fresh->verified_at = now();
            $fresh->save();

            OrderWorkflow::afterVerified($locked);
        });

        $order->refresh();
        $payment->refresh();
        Notify::customer($order, OrderNotification::paymentVerified($order, $payment));

        return back()->with('status', 'Pembayaran diverifikasi.');
    }

    public function rejectPayment(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        abort_unless($payment->order_id === $order->id, 404);

        $data = $request->validate(
            ['reject_reason' => ['required', 'string', 'max:500']],
            [],
            ['reject_reason' => 'alasan penolakan'],
        );

        DB::transaction(function () use ($request, $order, $payment, $data) {
            $locked = $this->lock($order);
            $fresh = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== VerificationStatus::Menunggu) {
                $this->fail('payment', 'Bukti ini sudah diperiksa sebelumnya.');
            }

            $fresh->status = VerificationStatus::Ditolak;
            $fresh->reject_reason = $data['reject_reason'];
            $fresh->verified_by = $request->user()->id;
            $fresh->verified_at = now();
            $fresh->save();

            OrderWorkflow::afterRejected($locked);
        });

        $order->refresh();
        $payment->refresh();
        Notify::customer($order, OrderNotification::paymentRejected($order, $payment));

        return back()->with('status', 'Bukti pembayaran ditolak. Pelanggan dapat mengunggah ulang.');
    }

    /** BR-08: admin menandai lunas (tunai atau transfer yang diterima di luar sistem). */
    public function markPaid(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(
            ['channel' => ['required', Rule::in(array_column(PaymentChannel::cases(), 'value'))]],
            [],
            ['channel' => 'cara pembayaran'],
        );

        DB::transaction(function () use ($request, $order, $data) {
            $locked = $this->lock($order);

            if ($locked->status === OrderStatus::Dibatalkan) {
                $this->fail('channel', 'Pesanan sudah dibatalkan.');
            }
            if (OrderWorkflow::hasPendingPayment($locked)) {
                $this->fail('channel', 'Ada bukti yang menunggu. Verifikasi atau tolak dulu.');
            }

            $remaining = $locked->remainingBalance();
            if ($remaining <= 0) {
                $this->fail('channel', 'Tidak ada sisa tagihan.');
            }

            $alreadyPaid = $locked->grand_total - $remaining;

            $payment = new Payment([
                'type' => ($alreadyPaid > 0 ? PaymentType::Pelunasan : PaymentType::Penuh)->value,
                'method' => $data['channel'],
                'proof_file' => null,
            ]);
            $payment->order_id = $locked->id;
            $payment->amount = $remaining;
            $payment->status = VerificationStatus::Terverifikasi;
            $payment->verified_by = $request->user()->id;
            $payment->verified_at = now();
            $payment->save();

            OrderWorkflow::afterVerified($locked);
        });

        $order->refresh();
        if ($paid = $order->payments()->latest('id')->first()) {
            Notify::customer($order, OrderNotification::paymentVerified($order, $paid));
        }

        return back()->with('status', 'Pesanan ditandai lunas.');
    }

    // ---------- status ----------

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(
            [
                'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
                // BR-07: resi wajib untuk status Dikirim.
                'tracking_no' => ['nullable', 'string', 'max:100', 'required_if:status,'.OrderStatus::Dikirim->value],
            ],
            [],
            ['status' => 'status', 'tracking_no' => 'nomor resi'],
        );

        $to = OrderStatus::from($data['status']);

        DB::transaction(function () use ($order, $to, $data) {
            $locked = $this->lock($order);

            if (! in_array($to, OrderWorkflow::allowedNext($locked), true)) {
                $this->fail('status', "Status {$locked->status->label()} tidak dapat diubah menjadi {$to->label()}.");
            }

            if ($reason = OrderWorkflow::blocker($locked, $to)) {
                $this->fail('status', $reason);
            }

            if ($to === OrderStatus::Dikirim) {
                $locked->tracking_no = $data['tracking_no'];
            }

            $locked->status = $to;
            $locked->save();
        });

        $order->refresh();
        Notify::customer($order, OrderNotification::statusChanged($order));

        return back()->with('status', "Status diubah menjadi {$to->label()}.");
    }

    // ---------- revisi dan desain final ----------

    public function updateRevision(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(
            [
                'revision_count' => ['required', 'integer', 'min:0', 'max:100'],
                'revision_fee' => ['required', 'integer', 'min:0', 'max:100000000'],
            ],
            [],
            ['revision_count' => 'jumlah revisi', 'revision_fee' => 'biaya revisi'],
        );

        DB::transaction(function () use ($order, $data) {
            $locked = $this->lock($order);

            if (OrderWorkflow::isClosed($locked)) {
                $this->fail('revision_fee', 'Pesanan sudah selesai atau dibatalkan.');
            }

            $locked->revision_count = $data['revision_count'];
            $locked->revision_fee = $data['revision_fee'];
            $locked->save(); // total tagihan berubah; status bayar disesuaikan

            OrderWorkflow::syncPaymentStatus($locked);
        });

        $order->refresh();
        Notify::customer($order, OrderNotification::revisionUpdated($order));

        return back()->with('status', 'Revisi disimpan. Total tagihan diperbarui.');
    }

    public function storeFinalDesign(Request $request, Order $order): RedirectResponse
    {
        $productId = $order->items()->value('product_id');

        $request->validate(
            [
                'area_id' => ['nullable', Rule::exists('print_areas', 'id')->where('product_id', $productId)],
                'file' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:10240'],
            ],
            [],
            ['area_id' => 'area', 'file' => 'file desain final'],
        );

        if (OrderWorkflow::isClosed($order)) {
            return back()->with('error', 'Pesanan sudah selesai atau dibatalkan.');
        }

        $area = $request->filled('area_id') ? PrintArea::find($request->input('area_id')) : null;
        $path = $request->file('file')->store("designs/{$order->id}/final", 'local');

        try {
            // area_price = 0: biaya area tidak dihitung ulang di sini; gunakan biaya revisi bila perlu.
            $order->designs()->create([
                'print_area_id' => $area?->id,
                'area_name' => $area?->name ?? 'Desain final',
                'print_size' => $area?->size_label,
                'area_price' => 0,
                'file' => $path,
                'type' => DesignFileType::from('final')->value,
            ]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        Notify::customer($order, OrderNotification::finalDesign($order));

        return back()->with('status', 'Desain final diunggah.');
    }

    public function destroyFinalDesign(Order $order, Design $design): RedirectResponse
    {
        abort_unless($design->order_id === $order->id && $design->type->value === 'final', 404);

        if (OrderWorkflow::isClosed($order)) {
            return back()->with('error', 'Pesanan sudah selesai atau dibatalkan.');
        }

        Storage::disk('local')->delete($design->file);
        $design->delete();

        return back()->with('status', 'Desain final dihapus.');
    }

    // ---------- pembatalan ----------

    /** BR-14: pembatalan wajib disertai alasan. Kebijakan pengembalian DP mengikuti mitra (belum ada). */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(
            ['cancel_reason' => ['required', 'string', 'min:5', 'max:500']],
            [],
            ['cancel_reason' => 'alasan pembatalan'],
        );

        DB::transaction(function () use ($order, $data) {
            $locked = $this->lock($order);

            if (! OrderWorkflow::canCancel($locked)) {
                $this->fail('cancel_reason', 'Pesanan ini sudah selesai atau sudah dibatalkan.');
            }

            $locked->status = OrderStatus::Dibatalkan;
            $locked->cancel_reason = $data['cancel_reason'];
            $locked->save();
        });

        $order->refresh();
        Notify::customer($order, OrderNotification::cancelled($order));

        return back()->with('status', 'Pesanan dibatalkan.');
    }

    // ---------- bantu ----------

    private function lock(Order $order): Order
    {
        return Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}