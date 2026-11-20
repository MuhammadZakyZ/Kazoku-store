<?php

namespace Tests\Feature;

use App\Enums\PaymentType;
use App\Models\Payment;
use App\Notifications\OrderNotification;
use App\Support\PaymentPlanner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\ShopTestCase;

class PaymentTest extends ShopTestCase
{
    private function proof(): UploadedFile
    {
        return UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
    }

    public function test_first_proof_is_dp_with_server_amount_and_moves_order_to_waiting(): void
    {
        Storage::fake('local');
        Notification::fake();
        $admin = $this->admin();
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($owner)->post(route('payments.store', $order), ['proof_file' => $this->proof(), 'amount' => 1, 'type' => 'penuh'])
            ->assertRedirect(route('orders.show', $order));

        $payment = Payment::firstOrFail();
        $this->assertSame(PaymentType::Dp, $payment->type);
        $this->assertSame(150000, $payment->amount);          // bukan 1 dari isian pelanggan
        $this->assertSame('menunggu', $payment->status->value);
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status->value);
        Storage::disk('local')->assertExists($payment->proof_file);
        Notification::assertSentTo($admin, OrderNotification::class);
    }

    public function test_second_proof_is_blocked_while_one_is_waiting(): void
    {
        Storage::fake('local');
        Notification::fake();
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($owner)->post(route('payments.store', $order), ['proof_file' => $this->proof()]);
        $this->actingAs($owner)->post(route('payments.store', $order), ['proof_file' => $this->proof()])
            ->assertSessionHas('error');

        $this->assertSame(1, Payment::count());
    }

    public function test_pay_at_end_cannot_upload_proof(): void
    {
        Storage::fake('local');
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop(), ['payment_method' => 'di_akhir', 'dp_amount' => 0]);

        $this->actingAs($owner)->post(route('payments.store', $order), ['proof_file' => $this->proof()])
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_only_owner_can_upload_proof(): void
    {
        Storage::fake('local');
        $owner = $this->customer();
        $other = $this->customer();
        $admin = $this->admin();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($other)->post(route('payments.store', $order), ['proof_file' => $this->proof()])->assertNotFound();
        $this->actingAs($admin)->post(route('payments.store', $order), ['proof_file' => $this->proof()])->assertNotFound();
        $this->assertSame(0, Payment::count());
    }

    public function test_proof_must_be_an_allowed_file_type(): void
    {
        Storage::fake('local');
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $this->actingAs($owner)->post(route('payments.store', $order), [
            'proof_file' => UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('proof_file');
    }

    public function test_planner_asks_for_remaining_balance_after_verified_dp(): void
    {
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop());

        $first = PaymentPlanner::next($order);
        $this->assertSame(PaymentType::Dp, $first['type']);
        $this->assertSame(150000, $first['amount']);

        $payment = new Payment(['type' => 'dp', 'method' => 'transfer']);
        $payment->order_id = $order->id;
        $payment->amount = 150000;
        $payment->status = \App\Enums\VerificationStatus::Terverifikasi;
        $payment->save();

        $next = PaymentPlanner::next($order->fresh());
        $this->assertSame(PaymentType::Pelunasan, $next['type']);
        $this->assertSame(150000, $next['amount']);
    }
}
