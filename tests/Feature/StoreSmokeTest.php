<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_blog_and_product_routes_render(): void
    {
        $this->seed();

        $this->get('/kitchen')->assertOk()->assertSee('Kitchen');
        $this->get('/blog')->assertOk()->assertSee('Our journal');
        $this->get('/products/airtight-food-storage-set')->assertOk()->assertSee('Airtight Food Storage Set');
    }

    public function test_catalog_filters_and_blog_search_are_server_backed(): void
    {
        $this->seed();

        $this->get('/kitchen?in_stock=1&max_price=20')
            ->assertOk()
            ->assertSee('Kitchen');

        $this->get('/blog?q=storage')
            ->assertOk()
            ->assertSee('Smart Storage Ideas');
    }

    public function test_guest_can_add_product_to_session_cart(): void
    {
        $this->seed();
        $product = Product::where('slug', 'airtight-food-storage-set')->firstOrFail();

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();

        $this->get('/cart')->assertOk()->assertSee('Airtight Food Storage Set')->assertSee('$68.00');
    }

    public function test_newsletter_subscription_is_persisted_and_idempotent(): void
    {
        $this->seed();

        $this->post('/newsletter', ['email' => 'hello@example.test'])->assertRedirect();
        $this->post('/newsletter', ['email' => 'hello@example.test'])->assertRedirect();

        $this->assertSame(1, NewsletterSubscriber::count());
    }
}
