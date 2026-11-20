<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\ShopTestCase;

class AdminWorkflowTest extends ShopTestCase
{
    private function waitingPayment(Order $order, int $amount = 150000, string $type = 'dp'): Payment
    {
        $payment = new Payment(['type' => $type, 'method' => 'transfer', 'proof_file' => 'proofs/x.pdf']);
        $payment->order_id = $order->id;
        $payment->amount = $amount;
        $payment->status = VerificationStatus::Menunggu;
        $payment->save();

        $order->status = \App\Enums\OrderStatus::MenungguVerifikasi;
        $order->save();

        return $payment;
    }

    public function test_verifying_dp_sets_payment_status_and_starts_processing(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());
        $payment = $this->waitingPayment($order);

        $this->actingAs($admin)->post(route('admin.orders.payments.verify', [$order, $payment]))
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('dp', $order->payment_status->value);
        $this->assertSame('diproses', $order->status->value);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
        Notification::assertSentTo($owner, OrderNotification::class);
    }

    public function test_verifying_the_same_proof_twice_is_refused(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop());
        $payment = $this->waitingPayment($order);

        $this->actingAs($admin)->post(route('admin.orders.payments.verify', [$order, $payment]));
        $this->actingAs($admin)->post(route('admin.orders.payments.verify', [$order, $payment]))
            ->assertSessionHasErrors('payment');
    }

    public function test_rejecting_needs_reason_and_returns_order_to_new(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());
        $payment = $this->waitingPayment($order);

        $this->actingAs($admin)->post(route('admin.orders.payments.reject', [$order, $payment]), ['reject_reason' => ''])
            ->assertSessionHasErrors('reject_reason');

        $this->actingAs($admin)->post(route('admin.orders.payments.reject', [$order, $payment]), ['reject_reason' => 'Nominal tidak sesuai'])
            ->assertSessionHasNoErrors();

        $this->assertSame('ditolak', $payment->fresh()->status->value);
        $this->assertSame('baru', $order->fresh()->status->value);
        Notification::assertSentTo($owner, OrderNotification::class);
    }

    public function test_mark_paid_records_remaining_balance_for_pay_at_end(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), ['payment_method' => 'di_akhir', 'dp_amount' => 0]);

        $this->actingAs($admin)->post(route('admin.orders.paid', $order), ['channel' => 'tunai'])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('lunas', $order->payment_status->value);
        $this->assertSame(0, $order->remainingBalance());
        $this->assertSame(300000, Payment::firstOrFail()->amount);

        // tidak ada sisa lagi
        $this->actingAs($admin)->post(route('admin.orders.paid', $order), ['channel' => 'tunai'])
            ->assertSessionHasErrors('channel');
    }

    public function test_processing_requires_verified_payment(): void
    {
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop());

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'diproses'])
            ->assertSessionHasErrors('status');

        $this->assertSame('baru', $order->fresh()->status->value);
    }

    public function test_consultation_needs_final_design_before_processing(): void
    {
        Storage::fake('local');
        Notification::fake();
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), ['design_type' => 'konsultasi', 'payment_method' => 'di_akhir', 'dp_amount' => 0]);

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'diproses'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->post(route('admin.orders.finalDesign.store', $order), [
            'file' => UploadedFile::fake()->create('final.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'diproses'])
            ->assertSessionHasNoErrors();

        $this->assertSame('diproses', $order->fresh()->status->value);
    }

    public function test_shipping_requires_tracking_number(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), [
            'status' => 'diproses', 'delivery_method' => 'kirim', 'payment_status' => 'dp',
        ]);

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'dikirim'])
            ->assertSessionHasErrors('tracking_no');

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'dikirim', 'tracking_no' => 'JNE12345'])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('dikirim', $order->status->value);
        $this->assertSame('JNE12345', $order->tracking_no);
    }

    public function test_pickup_order_cannot_be_marked_shipped(): void
    {
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), ['status' => 'diproses', 'payment_status' => 'dp']);

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'dikirim', 'tracking_no' => 'X1'])
            ->assertSessionHasErrors('status');
    }

    public function test_completing_requires_fully_paid(): void
    {
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), [
            'status' => 'siap_diambil', 'payment_status' => 'dp',
        ]);

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'selesai'])
            ->assertSessionHasErrors('status');

        $order->payment_status = \App\Enums\PaymentStatus::Lunas;
        $order->save();

        $this->actingAs($admin)->post(route('admin.orders.status', $order), ['status' => 'selesai'])
            ->assertSessionHasNoErrors();
        $this->assertSame('selesai', $order->fresh()->status->value);
    }

    public function test_cancel_needs_a_reason_and_closes_the_order(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($admin)->post(route('admin.orders.cancel', $order), ['cancel_reason' => 'x'])
            ->assertSessionHasErrors('cancel_reason');

        $this->actingAs($admin)->post(route('admin.orders.cancel', $order), ['cancel_reason' => 'Pelanggan membatalkan'])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('dibatalkan', $order->status->value);
        $this->assertSame('Pelanggan membatalkan', $order->cancel_reason);
        Notification::assertSentTo($owner, OrderNotification::class);

        // sudah dibatalkan: tidak bisa dibatalkan lagi
        $this->actingAs($admin)->post(route('admin.orders.cancel', $order), ['cancel_reason' => 'Coba lagi'])
            ->assertSessionHasErrors('cancel_reason');
    }

    public function test_revision_fee_raises_total_and_reopens_paid_order(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop(), ['payment_status' => 'lunas']);

        $paid = new Payment(['type' => 'penuh', 'method' => 'tunai']);
        $paid->order_id = $order->id;
        $paid->amount = 300000;
        $paid->status = VerificationStatus::Terverifikasi;
        $paid->save();

        $this->actingAs($admin)->post(route('admin.orders.revision', $order), ['revision_count' => 2, 'revision_fee' => 50000])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame(350000, $order->grand_total);
        $this->assertSame('dp', $order->payment_status->value);   // belum lunas lagi
        $this->assertSame(50000, $order->remainingBalance());
    }

    public function test_customer_can_confirm_receipt_only_when_shipped_and_paid(): void
    {
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop(), ['status' => 'dikirim', 'payment_status' => 'dp']);

        // belum lunas
        $this->actingAs($owner)->post(route('orders.received', $order))->assertSessionHas('error');
        $this->assertSame('dikirim', $order->fresh()->status->value);

        $paid = new Payment(['type' => 'penuh', 'method' => 'tunai']);
        $paid->order_id = $order->id;
        $paid->amount = 300000;
        $paid->status = VerificationStatus::Terverifikasi;
        $paid->save();

        $this->actingAs($owner)->post(route('orders.received', $order))->assertSessionHas('status');
        $this->assertSame('selesai', $order->fresh()->status->value);

        // pelanggan lain tidak bisa
        $other = $this->customer();
        $this->actingAs($other)->post(route('orders.received', $order))->assertNotFound();
    }
}
