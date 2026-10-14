<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
}
