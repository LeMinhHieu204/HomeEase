<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripePaymentGateway implements PaymentGateway
{
    public function createPayment(Order $order, string $idempotencyKey): PaymentResult
    {
        $secret = config('payments.stripe_secret');
        if (!$secret) throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET before accepting payments.');
        $response = Http::withBasicAuth($secret, '')->withHeaders(['Idempotency-Key' => $idempotencyKey])->asForm()->post('https://api.stripe.com/v1/payment_intents', ['amount' => (int) round((float) $order->grand_total * 100), 'currency' => strtolower($order->currency), 'metadata[order_number]' => $order->number, 'automatic_payment_methods[enabled]' => 'true']);
        if ($response->failed()) throw new RuntimeException('Stripe payment intent creation failed.');
        $payload = $response->json();
        return new PaymentResult($payload['status'] === 'succeeded' ? 'succeeded' : 'requires_action', $payload['id'] ?? null, $payload);
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        $secret = config('payments.stripe_webhook_secret');
        if (!$secret) throw new RuntimeException('Stripe webhook secret is not configured.');
        $parts = collect(explode(',', $signature))->mapWithKeys(function ($part) { [$key, $value] = array_pad(explode('=', $part, 2), 2, null); return [$key => $value]; });
        $timestamp = (int) ($parts->get('t') ?? 0);
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        abort_unless(hash_equals($expected, (string) $parts->get('v1')), 400, 'Invalid Stripe signature.');
        abort_unless(abs(time() - $timestamp) < 300, 400, 'Expired Stripe signature.');
        return json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
    }
}
