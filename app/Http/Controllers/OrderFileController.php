<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksOrderAccess;
use App\Models\Design;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class OrderFileController extends Controller
{
    use ChecksOrderAccess;

    /** File desain (pelanggan atau final): hanya pemilik dan admin; disk privat. */
    public function design(Request $request, Order $order, Design $design): Response
    {
        $this->authorizeOrder($request, $order);
        abort_unless($design->order_id === $order->id && Storage::disk('local')->exists($design->file), 404);

        return Storage::disk('local')->download(
            $design->file,
            $this->name($order, $design->area_name ?: 'desain', $design->file)
        );
    }

    /** Bukti pembayaran: hanya pemilik dan admin. */
    public function proof(Request $request, Order $order, Payment $payment): Response
    {
        $this->authorizeOrder($request, $order);
        abort_unless(
            $payment->order_id === $order->id && $payment->proof_file && Storage::disk('local')->exists($payment->proof_file),
            404
        );

        return Storage::disk('local')->download(
            $payment->proof_file,
            $this->name($order, 'bukti-'.$payment->type->value, $payment->proof_file)
        );
    }

    private function name(Order $order, string $label, string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return Str::slug($order->code.' '.$label).($ext ? ".{$ext}" : '');
    }
}
