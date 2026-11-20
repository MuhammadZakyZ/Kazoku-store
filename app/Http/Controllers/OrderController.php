<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryMethod;
use App\Enums\DesignFileType;
use App\Enums\DesignType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Notifications\OrderNotification;
use App\Support\Notify;
use App\Support\PaymentPlanner;
use Illuminate\Support\Facades\Gate;
use App\Support\Whatsapp;
use App\Http\Controllers\Concerns\ChecksOrderAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OrderController extends Controller
{
    use ChecksOrderAccess;

    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => Order::ownedBy($request->user())->with('items.product')->latest()->paginate(10),
        ]);
    }

    public function create(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'options', 'colors', 'printAreas']);

        $couriers = Courier::active()
            ->with(['shippingRates' => fn ($q) => $q->orderBy('destination')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->shippingRates->isNotEmpty())
            ->values();

        // Data untuk perhitungan dan preview di peramban. Hanya tampilan;
        // server selalu menghitung ulang (BR-04).
        $data = [
            'basePrice' => $product->base_price,
            'dpPercent' => (float) Setting::get('dp_percent', 0),
            'hasPreview' => $product->hasPreview(),
            'options' => $product->options->map(fn ($o) => [
                'id' => $o->id, 'name' => $o->name, 'extra' => $o->extra_price,
            ])->values(),
            'colors' => $product->colors->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name,
                'mockup' => $c->mockup_image ? route('media.mockup', $c) : null,
            ])->values(),
            'areas' => $product->printAreas->map(fn ($a) => [
                'id' => $a->id, 'name' => $a->name, 'label' => $a->size_label, 'price' => $a->price,
                'x' => $a->preview_x, 'y' => $a->preview_y, 'w' => $a->preview_w, 'h' => $a->preview_h,
            ])->values(),
            'couriers' => $couriers->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name,
                'rates' => $c->shippingRates->map(fn ($r) => [
                    'id' => $r->id, 'destination' => $r->destination, 'rate' => $r->rate,
                ])->values(),
            ])->values(),
            'old' => [
                'courier_id' => old('courier_id'),
                'shipping_rate_id' => old('shipping_rate_id'),
            ],
        ];

        return view('orders.create', compact('product', 'data'));
    }

    public function store(StoreOrderRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validated();

        $designType = DesignType::from($data['design_type']);
        $delivery = DeliveryMethod::from($data['delivery_method']);
        $payment = PaymentMethod::from($data['payment_method']);
        $quantity = (int) $data['quantity'];

        $option = filled($data['option_id'] ?? null) ? $product->options()->findOrFail($data['option_id']) : null;
        $color = filled($data['color_id'] ?? null) ? $product->colors()->findOrFail($data['color_id']) : null;

        $areas = $designType === DesignType::PunyaDesain
            ? $product->printAreas()->whereIn('id', $data['area_ids'])->orderBy('id')->get()
            : collect();

        $rate = null;
        if ($delivery === DeliveryMethod::Kirim) {
            $rate = ShippingRate::with('courier')
                ->where('courier_id', $data['courier_id'])
                ->findOrFail($data['shipping_rate_id']);
            abort_unless($rate->courier->is_active, 422);
        }

        $storedFiles = [];

        try {
            // BR-06: pesanan, rincian, dan desain tersimpan utuh atau tidak sama sekali.
            $order = DB::transaction(function () use ($request, $product, $option, $color, $areas, $rate, $designType, $delivery, $payment, $quantity, $data, &$storedFiles) {
                // BR-01 dan BR-05, dihitung di server (BR-04).
                $unitPrice = Order::unitPriceFor($product, $option, $areas);
                $shippingCost = $rate?->rate ?? 0;

                $order = new Order([
                    'design_type' => $designType->value,
                    'delivery_method' => $delivery->value,
                    'courier_id' => $rate?->courier_id,
                    'shipping_rate_id' => $rate?->id,
                    'shipping_destination' => $rate?->destination,
                    'shipping_address' => $delivery === DeliveryMethod::Kirim ? $data['shipping_address'] : null,
                    'payment_method' => $payment->value,
                ]);
                $order->user_id = $request->user()->id;
                $order->shipping_cost = $shippingCost;
                $order->save(); // kode pesanan unik terbit di sini

                // Snapshot nama dan harga supaya perubahan data master tidak mengubah pesanan lama.
                $order->items()->create([
                    'product_id' => $product->id,
                    'option_name' => $option?->name,
                    'color_name' => $color?->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $total = $unitPrice * $quantity + $shippingCost;
                $order->total = $total;
                // BR-02: DP hanya terisi bila metode pembayaran DP.
                $order->dp_amount = $payment === PaymentMethod::Dp ? Order::dpAmountFor($total) : 0;
                $order->save();

                foreach ($areas as $area) {
                    $file = $request->file("designs.{$area->id}");
                    $path = $file->store("designs/{$order->id}", 'local');
                    $storedFiles[] = $path;

                    $order->designs()->create([
                        'print_area_id' => $area->id,
                        'area_name' => $area->name,
                        'print_size' => $area->size_label,
                        'area_price' => $area->price,
                        'file' => $path,
                        'type' => DesignFileType::Pelanggan->value,
                    ]);
                }

                return $order;
            });
        } catch (Throwable $e) {
            foreach ($storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        // BR-15: kabari semua admin (gagal kirim email tidak menggagalkan pesanan).
        Notify::admins(OrderNotification::newOrder($order->load('user')));

        return redirect()->route('orders.show', $order)
            ->with('status', "Pesanan berhasil dibuat. Kode pesanan Anda: {$order->code}.");
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['items.product', 'designs', 'courier', 'payments.verifier']);

        $upfront = match ($order->payment_method) {
            PaymentMethod::Dp => $order->dp_amount,
            PaymentMethod::PenuhDiAwal => $order->grand_total,
            PaymentMethod::DiAkhir => 0,
        };

        return view('orders.show', [
            'order' => $order,
            'upfront' => $upfront,
            'nextPayment' => PaymentPlanner::next($order),
            'canConfirm' => in_array($order->status, [OrderStatus::Dikirim, OrderStatus::SiapDiambil], true),
            'paymentInstructions' => Setting::get('payment_instructions'),
            'whatsapp' => Whatsapp::link(
                Setting::get('whatsapp_number'),
                "Halo Kazoku Store, saya ingin konsultasi untuk pesanan {$order->code}."
            ),
        ]);
    }

    public function invoice(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['user', 'items.product', 'designs', 'courier', 'payments']);

        return view('orders.invoice', [
            'order' => $order,
            'address' => Setting::get('address'),
            'openingHours' => Setting::get('opening_hours'),
            'whatsappNumber' => Setting::get('whatsapp_number'),
        ]);
    }

    /** Pelanggan mengonfirmasi pesanan sudah diterima atau diambil. */
    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('confirmReceived', $order);

        if (! in_array($order->status, [OrderStatus::Dikirim, OrderStatus::SiapDiambil], true)) {
            return back()->with('error', 'Pesanan ini belum bisa dikonfirmasi.');
        }

        if ($order->remainingBalance() > 0) {
            return back()->with('error', 'Selesaikan sisa tagihan terlebih dahulu sebelum mengonfirmasi.');
        }

        $order->status = OrderStatus::Selesai;
        $order->save();

        return back()->with('status', 'Terima kasih. Pesanan ditandai selesai.');
    }
}