<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CartService $cart, private readonly PaymentManager $payments) {}

    public function place(array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $items = $this->cart->items();
            if ($items->isEmpty()) throw ValidationException::withMessages(['cart' => 'Your basket is empty.']);
            $subtotal = $this->cart->subtotal();
            $couponCode = $data['coupon_code'] ?? null;
            $coupon = !empty($couponCode) ? Coupon::where('code', strtoupper($couponCode))->lockForUpdate()->first() : null;
            if ($couponCode && (!$coupon || !$coupon->isValidFor($subtotal))) throw ValidationException::withMessages(['coupon_code' => 'This coupon is not valid for your basket.']);
            $discount = $coupon?->discountFor($subtotal) ?? 0;
            $shipping = ($data['shipping_method'] ?? 'standard') === 'express' ? 14.00 : (($subtotal - $discount) >= 49 ? 0 : 6.99);
            $total = round($subtotal - $discount + $shipping, 2);
            $order = Order::create(['user_id' => $user?->id, 'number' => 'HE-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)), 'status' => 'awaiting_payment', 'payment_status' => 'pending', 'currency' => 'USD', 'subtotal' => $subtotal, 'discount_total' => $discount, 'shipping_total' => $shipping, 'tax_total' => 0, 'grand_total' => $total, 'email' => $data['email'], 'shipping_method' => $data['shipping_method'] ?? 'standard', 'coupon_code' => $coupon?->code, 'shipping_address' => $data['shipping_address'], 'billing_address' => $data['shipping_address'], 'placed_at' => now()]);

            foreach ($items as $item) {
                $inventory = Inventory::query()->lockForUpdate()->where('product_id', $item->product_id)->when($item->variant_id, fn ($q) => $q->where('variant_id', $item->variant_id), fn ($q) => $q->whereNull('variant_id'))->first();
                if (!$inventory || $inventory->available < $item->quantity) throw ValidationException::withMessages(['cart' => "Stock changed for {$item->product->name}. Please review your basket."]);
                $inventory->decrement('quantity', $item->quantity);
                $item->product->decrement('stock', $item->quantity);
                $order->items()->create(['product_id' => $item->product_id, 'variant_id' => $item->variant_id, 'product_name' => $item->product->name, 'sku' => $item->variant?->sku ?? $item->product->sku, 'unit_price' => $item->cart_price, 'quantity' => $item->quantity, 'line_total' => $item->cart_line_total, 'options' => $item->variant?->options]);
            }

            $idempotencyKey = (string) Str::uuid();
            $payment = $order->payment()->create(['provider' => config('payments.driver'), 'status' => 'pending', 'amount' => $total, 'currency' => 'USD', 'idempotency_key' => $idempotencyKey]);
            $result = $this->payments->gateway()->createPayment($order, $idempotencyKey);
            $payment->update(['provider_payment_id' => $result->providerPaymentId, 'status' => $result->status, 'raw_response' => $result->payload, 'paid_at' => $result->succeeded() ? now() : null]);
            $order->update(['payment_status' => $result->succeeded() ? 'paid' : 'requires_action', 'status' => $result->succeeded() ? 'processing' : 'awaiting_payment']);
            Shipment::create(['order_id' => $order->id, 'status' => 'pending']);
            if ($coupon) { $coupon->increment('uses_count'); CouponRedemption::create(['coupon_id' => $coupon->id, 'user_id' => $user?->id, 'order_id' => $order->id]); }
            $this->cart->clear();
            return $order->fresh(['items', 'payment']);
        });
    }
}
