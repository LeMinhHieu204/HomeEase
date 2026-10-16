<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationService
{
    public function __construct(private readonly OrderService $orders) {}

    public function handle(array $event): array
    {
        $eventId = $event['id'] ?? null;
        if (! is_string($eventId) || $eventId === '') abort(400, 'Payment event id is required.');

        return DB::transaction(function () use ($event, $eventId): array {
            $inserted = DB::table('payment_events')->insertOrIgnore([
                'payment_id' => null,
                'provider' => 'stripe',
                'event_id' => $eventId,
                'type' => $event['type'] ?? 'unknown',
                'payload' => json_encode($event, JSON_THROW_ON_ERROR),
                'processed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted === 0) return ['duplicate' => true, 'processed' => false];

            $record = PaymentEvent::query()->where('event_id', $eventId)->lockForUpdate()->firstOrFail();
            $kind = $this->eventKind($event['type'] ?? '');
            if ($kind === 'ignored') {
                $record->update(['processed_at' => now()]);
                return ['duplicate' => false, 'processed' => false, 'ignored' => true];
            }

            $details = $this->details($event);
            $payment = $this->findPayment($details);
            if (! $payment) {
                $record->update(['processed_at' => now()]);
                return ['duplicate' => false, 'processed' => false, 'unmatched' => true];
            }
            $order = $payment->order()->lockForUpdate()->firstOrFail();
            $record->update(['payment_id' => $payment->id]);

            if (in_array($kind, ['paid', 'refunded'], true)) $this->assertAmountAndCurrency($details, $payment, $order, $kind);

            if ($kind === 'paid') {
                if (in_array($payment->status, ['refunded', 'succeeded'], true) || $order->status === 'cancelled') abort(409, 'The order is no longer payable.');
                $payment->update(['status' => 'succeeded', 'provider_payment_id' => $details['provider_reference'], 'raw_response' => $event, 'paid_at' => now()]);
                $order->update(['payment_status' => 'paid', 'status' => 'processing']);
                Shipment::firstOrCreate(['order_id' => $order->id], ['status' => 'pending']);
            } elseif ($kind === 'pending') {
                if (! in_array($payment->status, ['succeeded', 'refunded'], true)) {
                    $payment->update(['status' => 'requires_action', 'provider_payment_id' => $details['provider_reference'], 'raw_response' => $event]);
                    $order->update(['payment_status' => 'requires_action', 'status' => 'awaiting_payment']);
                }
            } elseif ($kind === 'failed') {
                if (! in_array($payment->status, ['succeeded', 'refunded'], true)) {
                    $payment->update(['status' => 'failed', 'provider_payment_id' => $details['provider_reference'], 'raw_response' => $event]);
                    $this->orders->releaseReservation($order, 'Payment failed or checkout expired.');
                }
            } else {
                $refundAmount = $details['refund_amount_cents'] ?? $details['amount_cents'];
                $status = $refundAmount !== null && $refundAmount >= $this->amountInCents($payment->amount) ? 'refunded' : 'partially_refunded';
                $payment->update(['status' => $status, 'provider_payment_id' => $details['provider_reference'], 'raw_response' => $event]);
                $order->update(['payment_status' => $status]);
            }

            $record->update(['processed_at' => now()]);
            return ['duplicate' => false, 'processed' => true, 'status' => $kind === 'refunded' ? $payment->status : $kind];
        });
    }

    private function eventKind(string $type): string
    {
        return match ($type) {
            'payment_intent.succeeded', 'checkout.session.completed', 'checkout.session.async_payment_succeeded' => 'paid',
            'payment_intent.processing' => 'pending',
            'payment_intent.payment_failed', 'payment_intent.canceled', 'checkout.session.async_payment_failed', 'checkout.session.expired' => 'failed',
            'charge.refunded' => 'refunded',
            default => 'ignored',
        };
    }

    private function details(array $event): array
    {
        $object = $event['data']['object'] ?? [];
        $metadata = $object['metadata'] ?? [];
        return [
            'order_number' => $metadata['order_number'] ?? $metadata['orderNumber'] ?? null,
            'provider_reference' => $object['payment_intent'] ?? $object['id'] ?? null,
            'amount_cents' => $object['amount_received'] ?? $object['amount_total'] ?? $object['amount'] ?? null,
            'refund_amount_cents' => $object['amount_refunded'] ?? null,
            'currency' => isset($object['currency']) ? strtoupper((string) $object['currency']) : null,
        ];
    }

    private function findPayment(array $details): ?Payment
    {
        if (! $details['order_number'] && ! $details['provider_reference']) return null;
        return Payment::query()->where(function ($query) use ($details): void {
            $started = false;
            if ($details['order_number']) {
                $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('number', $details['order_number']));
                $started = true;
            }
            if ($details['provider_reference']) {
                $started ? $query->orWhere('provider_payment_id', $details['provider_reference']) : $query->where('provider_payment_id', $details['provider_reference']);
            }
        })->lockForUpdate()->first();
    }

    private function assertAmountAndCurrency(array $details, Payment $payment, Order $order, string $kind): void
    {
        $amount = $kind === 'refunded' ? ($details['refund_amount_cents'] ?? $details['amount_cents']) : $details['amount_cents'];
        $expected = $this->amountInCents($payment->amount);
        if (! is_numeric($amount) || (int) $amount !== $amount || (int) $amount <= 0 || ($kind === 'paid' && (int) $amount !== $expected) || ($kind === 'refunded' && (int) $amount > $expected)) abort(400, 'The provider amount does not match the payment.');
        if ($details['currency'] && strtoupper($order->currency ?: 'USD') !== $details['currency']) abort(400, 'The provider currency does not match the payment.');
    }

    private function amountInCents($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
