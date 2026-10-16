<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_webhook_signature_is_verified_and_event_is_idempotent(): void
    {
        Config::set('payments.driver', 'stripe');
        Config::set('payments.stripe_webhook_secret', 'whsec_test');
        $user = User::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'number' => 'HE-TEST-001', 'status' => 'awaiting_payment', 'payment_status' => 'pending', 'currency' => 'USD', 'subtotal' => 10, 'discount_total' => 0, 'shipping_total' => 6.99, 'tax_total' => 0, 'grand_total' => 16.99, 'email' => $user->email, 'shipping_method' => 'standard', 'shipping_address' => ['city' => 'Austin']]);
        Payment::create(['order_id' => $order->id, 'provider' => 'stripe', 'provider_payment_id' => 'pi_test', 'status' => 'pending', 'amount' => 16.99, 'currency' => 'USD', 'idempotency_key' => 'idem-test']);
        $payload = json_encode(['id' => 'evt_test_001', 'type' => 'payment_intent.succeeded', 'data' => ['object' => ['id' => 'pi_test', 'amount_received' => 1699, 'currency' => 'usd', 'metadata' => ['order_number' => $order->number]]]], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test');

        $this->postJson('/webhooks/stripe', json_decode($payload, true, flags: JSON_THROW_ON_ERROR), ['Stripe-Signature' => $signature])->assertOk();
        $this->postJson('/webhooks/stripe', json_decode($payload, true, flags: JSON_THROW_ON_ERROR), ['Stripe-Signature' => $signature])->assertOk();

        $this->assertDatabaseHas('payments', ['provider_payment_id' => 'pi_test', 'status' => 'succeeded']);
        $this->assertDatabaseHas('orders', ['number' => 'HE-TEST-001', 'payment_status' => 'paid', 'status' => 'processing']);
        $this->assertDatabaseHas('shipments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('payment_events', 1);
    }
}
