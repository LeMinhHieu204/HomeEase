<?php

namespace App\Services\Payments;

final class PaymentResult
{
    public function __construct(public readonly string $status, public readonly ?string $providerPaymentId = null, public readonly array $payload = []) {}
    public function succeeded(): bool { return $this->status === 'succeeded'; }
}
