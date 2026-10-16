<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_paid_order_and_decrements_inventory(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::where('slug', 'airtight-food-storage-set')->firstOrFail();
        $before = Inventory::where('product_id', $product->id)->whereNull('variant_id')->value('quantity');

        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect();
        $response = $this->actingAs($user)->post('/checkout', ['email' => $user->email, 'recipient_name' => 'Ava Home', 'phone' => '555-0101', 'line_1' => '1 Home Street', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78701', 'country' => 'US', 'shipping_method' => 'standard']);
        $order = Order::latest('id')->firstOrFail();

        $response->assertRedirect(route('checkout.success', $order));
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame($before - 2, (int) Inventory::where('product_id', $product->id)->whereNull('variant_id')->value('quantity'));
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'quantity' => 2]);
    }

    public function test_coupon_is_applied_by_server_side_order_service(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $product = Product::where('slug', 'oak-side-table')->firstOrFail();
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($user)->post('/checkout', ['email' => $user->email, 'recipient_name' => 'Ava Home', 'phone' => '555-0101', 'line_1' => '1 Home Street', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78701', 'country' => 'US', 'shipping_method' => 'standard', 'coupon_code' => 'WELCOME10']);
        $order = Order::latest('id')->firstOrFail();
        $this->assertGreaterThan(0, (float) $order->discount_total);
        $this->assertSame('WELCOME10', $order->coupon_code);
    }

    public function test_failed_payment_webhook_releases_reserved_inventory_once_and_creates_no_shipment(): void
    {
        $this->seed();
        config(['payments.driver' => 'stripe', 'payments.stripe_webhook_secret' => 'whsec_test']);
        $user = User::factory()->create();
        $product = Product::where('slug', 'airtight-food-storage-set')->firstOrFail();
        $inventory = Inventory::where('product_id', $product->id)->whereNull('variant_id')->firstOrFail();
        $inventory->update(['quantity' => 8]);
        $product->update(['stock' => 8]);
        $order = Order::create(['user_id' => $user->id, 'number' => 'HE-FAIL-001', 'status' => 'awaiting_payment', 'payment_status' => 'requires_action', 'currency' => 'USD', 'subtotal' => 20, 'discount_total' => 0, 'shipping_total' => 6.99, 'tax_total' => 0, 'grand_total' => 26.99, 'email' => $user->email, 'shipping_method' => 'standard', 'shipping_address' => ['city' => 'Austin'], 'payment_expires_at' => now()->addMinutes(20)]);
        $order->items()->create(['product_id' => $product->id, 'variant_id' => null, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => 10, 'quantity' => 2, 'line_total' => 20]);
        $inventory->decrement('quantity', 2);
        $product->decrement('stock', 2);
        Payment::create(['order_id' => $order->id, 'provider' => 'stripe', 'provider_payment_id' => 'pi_fail', 'status' => 'requires_action', 'amount' => 26.99, 'currency' => 'USD', 'idempotency_key' => 'idem-fail']);
        $payload = json_encode(['id' => 'evt_fail_001', 'type' => 'payment_intent.payment_failed', 'data' => ['object' => ['id' => 'pi_fail', 'metadata' => ['order_number' => $order->number]]]], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

        $this->postJson('/webhooks/stripe', json_decode($payload, true, flags: JSON_THROW_ON_ERROR), ['Stripe-Signature' => $signature])->assertOk()->assertJson(['processed' => true, 'status' => 'failed']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled', 'payment_status' => 'failed']);
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 8]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseMissing('shipments', ['order_id' => $order->id]);

        $this->postJson('/webhooks/stripe', json_decode($payload, true, flags: JSON_THROW_ON_ERROR), ['Stripe-Signature' => $signature])->assertOk()->assertJson(['duplicate' => true]);
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 8]);
    }
}
