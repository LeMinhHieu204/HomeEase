<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    public function createPayment(Order $order, string $idempotencyKey): PaymentResult
    {
        abort_unless(app()->environment('testing'), 503, 'Fake payments are available only in the test environment.');
        return new PaymentResult('succeeded', 'fake_' . Str::lower(Str::random(16)), ['test' => true, 'order' => $order->number]);
    }

    public function verifyWebhook(string $payload, string $signature): array { return ['type' => 'fake.payment', 'data' => json_decode($payload, true) ?: []]; }
}
