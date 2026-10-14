<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentManager
{
    public function gateway(): PaymentGateway
    {
        return match (config('payments.driver')) {
            'fake' => app(FakePaymentGateway::class),
            'stripe' => app(StripePaymentGateway::class),
            default => throw new InvalidArgumentException('Unsupported payment driver.'),
        };
    }
}
