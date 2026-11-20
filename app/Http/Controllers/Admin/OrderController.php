<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Support\OrderWorkflow;
use App\Support\Whatsapp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, array_column(OrderStatus::cases(), 'value'), true) ? $status : null;
        $q = trim((string) $request->query('q', ''));

        $orders = Order::with(['user', 'items.product'])
            ->withCount(['payments as waiting_payments_count' => fn ($x) => $x->where('status', VerificationStatus::Menunggu->value)])
            ->withStatus($status)
            ->when($q !== '', fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function show(Order $order): View
    {
        $order = Order::withSpecification()->findOrFail($order->id);

        $statusOptions = collect(OrderWorkflow::allowedNext($order))->map(fn (OrderStatus $to) => [
            'value' => $to->value,
            'label' => $to->label(),
            'blocker' => OrderWorkflow::blocker($order, $to),
        ])->values();

        return view('admin.orders.show', [
            'order' => $order,
            'remaining' => $order->remainingBalance(),
            'customerWhatsapp' => Whatsapp::link(
                $order->user->phone,
                "Halo {$order->user->name}, kami dari Kazoku Store terkait pesanan {$order->code}."
            ),
            'statusOptions' => $statusOptions,
            'pending' => OrderWorkflow::hasPendingPayment($order),
            'closed' => OrderWorkflow::isClosed($order),
            'canCancel' => OrderWorkflow::canCancel($order),
            'areas' => $order->items->first()?->product?->printAreas()->orderBy('name')->get() ?? collect(),
            'freeRevisionLimit' => Setting::get('free_revision_limit'),
        ]);
    }
}
