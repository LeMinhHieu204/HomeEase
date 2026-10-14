<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_crud(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin/products')->assertForbidden();
    }

    public function test_super_admin_can_create_product_and_blog_post(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $category = Category::where('slug', 'kitchen')->firstOrFail();
        $blogCategory = BlogCategory::firstOrFail();

        $this->actingAs($admin)->post('/admin/products', ['name' => 'Admin Created Organizer', 'sku' => 'HE-ADMIN-001', 'category_id' => $category->id, 'description' => 'A real admin-created catalog item.', 'price' => 29, 'image_url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=900&q=85', 'material' => 'Bamboo', 'color' => 'Natural', 'stock' => 9, 'is_published' => 1])->assertRedirect(route('admin.products'));
        $product = Product::where('sku', 'HE-ADMIN-001')->firstOrFail();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_published' => 1]);
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 9]);

        $this->actingAs($admin)->post('/admin/blog-posts', ['title' => 'Admin Journal Note', 'blog_category_id' => $blogCategory->id, 'excerpt' => 'A real CMS post.', 'body' => '<p>Sanitized content placeholder.</p>', 'image_url' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=900&q=85', 'author' => 'HomeEase Team', 'reading_time' => 5, 'is_published' => 1])->assertRedirect(route('admin.blog'));
        $this->assertDatabaseHas('blog_posts', ['slug' => 'admin-journal-note', 'is_published' => 1]);
    }

    public function test_super_admin_can_update_inventory_and_store_audit_log(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $inventory = Inventory::whereNull('variant_id')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.inventory.update', $inventory), ['quantity' => 3, 'reorder_level' => 5])->assertRedirect();
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 3]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'inventory.updated', 'target_id' => $inventory->id]);
    }

    public function test_admin_resource_surfaces_render_and_write_real_records(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'super_admin']);

        foreach (['admin.categories', 'admin.brands', 'admin.variants', 'admin.coupons', 'admin.customers', 'admin.blog-categories', 'admin.banners', 'admin.media', 'admin.settings', 'admin.audit-logs'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
        foreach (['admin.categories.create', 'admin.brands.create', 'admin.variants.create', 'admin.coupons.create', 'admin.blog-categories.create', 'admin.banners.create', 'admin.media.create', 'admin.settings.create'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->actingAs($admin)->post(route('admin.coupons.store'), ['code' => 'ADMIN15', 'type' => 'percent', 'value' => 15, 'minimum_subtotal' => 20, 'is_active' => 1])->assertRedirect(route('admin.coupons'));
        $this->assertDatabaseHas('coupons', ['code' => 'ADMIN15']);

        $product = Product::where('slug', 'airtight-food-storage-set')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.products.images', $product))->assertOk();
        $this->actingAs($admin)->post(route('admin.products.images.store', $product), ['url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=900&q=85', 'alt_text' => 'Admin image', 'sort_order' => 1])->assertRedirect();
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id, 'sort_order' => 1]);
        $this->actingAs($admin)->post(route('admin.variants.store'), ['product_id' => $product->id, 'sku' => 'HE-ADMIN-V1', 'name' => 'Admin variant', 'price' => 39, 'options' => '{"size":"XL"}', 'stock' => 7, 'is_active' => 1])->assertRedirect(route('admin.variants'));
        $this->assertDatabaseHas('product_variants', ['sku' => 'HE-ADMIN-V1']);
        $this->assertDatabaseHas('inventories', ['quantity' => 7, 'variant_id' => ProductVariant::where('sku', 'HE-ADMIN-V1')->value('id')]);
    }
}
