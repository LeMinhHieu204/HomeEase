<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    public function createPayment(Order $order, string $idempotencyKey): PaymentResult;
    public function verifyWebhook(string $payload, string $signature): array;
}
