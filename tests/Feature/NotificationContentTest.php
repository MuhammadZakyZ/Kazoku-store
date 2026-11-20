<?php

namespace Tests\Feature;

use App\Notifications\OrderNotification;
use Tests\ShopTestCase;

class NotificationContentTest extends ShopTestCase
{
    public function test_emails_mention_the_order_code_and_link_to_the_order(): void
    {
        $owner = $this->customer();
        $order = $this->makeOrder($owner, $this->shop(), ['status' => 'dikirim', 'tracking_no' => 'JNE999']);

        $mail = OrderNotification::statusChanged($order)->toMail($owner);

        $this->assertStringContainsString($order->code, $mail->subject);
        $this->assertSame(route('orders.show', $order), $mail->actionUrl);
        $this->assertContains('Nomor resi: JNE999.', $mail->introLines);
    }

    public function test_admin_email_links_to_admin_page(): void
    {
        $admin = $this->admin();
        $order = $this->makeOrder($this->customer(), $this->shop());

        $mail = OrderNotification::newOrder($order->load('user'))->toMail($admin);

        $this->assertSame(route('admin.orders.show', $order), $mail->actionUrl);
    }

    public function test_notification_is_queued(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new OrderNotification('s', [], 'a', 'https://contoh.test'));
    }
}
