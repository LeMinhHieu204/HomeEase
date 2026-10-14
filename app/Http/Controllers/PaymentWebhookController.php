<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function stripe(Request $request, PaymentManager $manager): Response
    {
        $payload = $request->getContent();
        $event = $manager->gateway()->verifyWebhook($payload, (string) $request->header('Stripe-Signature'));
        $eventId = $event['id'] ?? hash('sha256', $payload);
        if (PaymentEvent::where('event_id', $eventId)->exists()) return response(['received' => true]);
        $object = $event['data']['object'] ?? [];
        $payment = Payment::where('provider_payment_id', $object['id'] ?? null)->first();
        $stored = PaymentEvent::create(['payment_id' => $payment?->id, 'provider' => 'stripe', 'event_id' => $eventId, 'type' => $event['type'] ?? 'unknown', 'payload' => $event, 'processed_at' => now()]);
        if ($payment && in_array($event['type'] ?? '', ['payment_intent.succeeded', 'payment_intent.payment_failed'], true)) {
            $succeeded = $event['type'] === 'payment_intent.succeeded';
            $payment->update(['status' => $succeeded ? 'succeeded' : 'failed', 'paid_at' => $succeeded ? now() : null]);
            $payment->order()->update(['payment_status' => $succeeded ? 'paid' : 'failed', 'status' => $succeeded ? 'processing' : 'cancelled']);
        }
        return response(['received' => true]);
    }
}
