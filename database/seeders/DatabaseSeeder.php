<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kitchen', 'slug' => 'kitchen', 'description' => 'Thoughtful tools and storage for your everyday kitchen.', 'image_url' => 'https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Storage', 'slug' => 'storage', 'description' => 'Calm, clever ways to make space for what matters.', 'image_url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Cleaning', 'slug' => 'cleaning', 'description' => 'Simple routines made lighter with better tools.', 'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Bathroom', 'slug' => 'bathroom', 'description' => 'Small upgrades for a more considered daily ritual.', 'image_url' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Living', 'slug' => 'living', 'description' => 'Comfort-first pieces for rooms that feel like home.', 'image_url' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Dining', 'slug' => 'dining', 'description' => 'Everyday pieces for meals worth lingering over.', 'image_url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Decor', 'slug' => 'decor', 'description' => 'Natural texture and quiet character for every corner.', 'image_url' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Home Appliances', 'slug' => 'home-appliances', 'description' => 'Modern helpers that quietly give you time back.', 'image_url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=900&q=85'],
        ];

        $categoryModels = collect($categories)->mapWithKeys(function (array $data) {
            return [$data['slug'] => Category::updateOrCreate(['slug' => $data['slug']], $data)];
        });

        $productSets = [
            'kitchen' => [
                ['name' => 'Airtight Food Storage Set', 'price' => 34, 'compare' => 42, 'material' => 'Glass', 'color' => 'Clear'],
                ['name' => 'Kitchen Utensil Holder', 'price' => 28, 'compare' => null, 'material' => 'Ceramic', 'color' => 'Sand'],
                ['name' => 'Bamboo Cutting Board Set', 'price' => 32, 'compare' => 38, 'material' => 'Bamboo', 'color' => 'Natural'],
                ['name' => 'Glass Oil Dispenser', 'price' => 18, 'compare' => null, 'material' => 'Glass', 'color' => 'Clear'],
                ['name' => 'Spice Jar Set with Rack', 'price' => 44, 'compare' => 55, 'material' => 'Glass', 'color' => 'Black'],
                ['name' => 'Dish Drying Rack', 'price' => 46, 'compare' => null, 'material' => 'Stainless Steel', 'color' => 'Stone'],
            ],
            'storage' => [
                ['name' => 'Vacuum Storage Bags', 'price' => 22, 'compare' => 28, 'material' => 'Plastic', 'color' => 'Clear'],
                ['name' => 'Wall Mounted Organizer Shelf', 'price' => 39, 'compare' => null, 'material' => 'Bamboo', 'color' => 'Natural'],
                ['name' => 'Woven Catch-All Basket', 'price' => 29, 'compare' => 35, 'material' => 'Bamboo', 'color' => 'Natural'],
                ['name' => 'Stackable Pantry Bins', 'price' => 26, 'compare' => null, 'material' => 'Plastic', 'color' => 'Clear'],
            ],
            'cleaning' => [
                ['name' => '360° Spin Mop & Bucket Set', 'price' => 58, 'compare' => 69, 'material' => 'Plastic', 'color' => 'Sage'],
                ['name' => 'Microfiber Cleaning Cloths', 'price' => 16, 'compare' => null, 'material' => 'Cotton', 'color' => 'Multi'],
                ['name' => 'Natural Dish Brush Set', 'price' => 19, 'compare' => 24, 'material' => 'Bamboo', 'color' => 'Natural'],
                ['name' => 'Refillable Spray Bottle', 'price' => 17, 'compare' => null, 'material' => 'Glass', 'color' => 'Amber'],
            ],
            'bathroom' => [
                ['name' => 'Bathroom Accessories Set', 'price' => 42, 'compare' => 50, 'material' => 'Ceramic', 'color' => 'Cream'],
                ['name' => 'Stoneware Soap Dispenser', 'price' => 24, 'compare' => null, 'material' => 'Ceramic', 'color' => 'Sand'],
                ['name' => 'Over-Door Towel Rack', 'price' => 34, 'compare' => 39, 'material' => 'Stainless Steel', 'color' => 'Black'],
                ['name' => 'Linen Hand Towel Pair', 'price' => 22, 'compare' => null, 'material' => 'Cotton', 'color' => 'Oat'],
            ],
            'living' => [
                ['name' => 'Textured Throw Blanket', 'price' => 68, 'compare' => 84, 'material' => 'Cotton', 'color' => 'Oat'],
                ['name' => 'Sculptural Table Lamp', 'price' => 72, 'compare' => null, 'material' => 'Ceramic', 'color' => 'Cream'],
                ['name' => 'Soft Bouclé Cushion', 'price' => 26, 'compare' => 31, 'material' => 'Cotton', 'color' => 'Ivory'],
                ['name' => 'Oak Side Table', 'price' => 128, 'compare' => 149, 'material' => 'Wood', 'color' => 'Oak'],
            ],
            'dining' => [
                ['name' => 'Stoneware Serving Bowl', 'price' => 36, 'compare' => null, 'material' => 'Ceramic', 'color' => 'Cream'],
                ['name' => 'Linen Napkin Set', 'price' => 24, 'compare' => 29, 'material' => 'Linen', 'color' => 'Oat'],
                ['name' => 'Acacia Serving Board', 'price' => 39, 'compare' => null, 'material' => 'Wood', 'color' => 'Natural'],
            ],
            'decor' => [
                ['name' => 'Handmade Ceramic Vase', 'price' => 48, 'compare' => 58, 'material' => 'Ceramic', 'color' => 'Clay'],
                ['name' => 'Scented Soy Candle', 'price' => 24, 'compare' => null, 'material' => 'Soy Wax', 'color' => 'Cream'],
                ['name' => 'Pleated Linen Lampshade', 'price' => 54, 'compare' => 68, 'material' => 'Linen', 'color' => 'Oat'],
            ],
            'home-appliances' => [
                ['name' => 'Compact Air Purifier', 'price' => 119, 'compare' => 139, 'material' => 'ABS', 'color' => 'White'],
                ['name' => 'Quiet Electric Kettle', 'price' => 64, 'compare' => null, 'material' => 'Stainless Steel', 'color' => 'Cream'],
                ['name' => 'Handheld Garment Steamer', 'price' => 79, 'compare' => 94, 'material' => 'ABS', 'color' => 'Sage'],
            ],
        ];

        $categoryModels->each(function (Category $category, string $categorySlug) use ($productSets) {
            foreach ($productSets[$categorySlug] ?? [] as $index => $product) {
                $slug = Str::slug($product['name']);
                Product::updateOrCreate(['slug' => $slug], [
                    'category_id' => $category->id, 'name' => $product['name'], 'slug' => $slug,
                    'sku' => 'HE-' . strtoupper(Str::substr($categorySlug, 0, 3)) . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'description' => 'A considered everyday essential designed to make home life feel a little lighter. Thoughtful materials, practical details and a warm, timeless finish.',
                    'price' => $product['price'], 'compare_at_price' => $product['compare'], 'image_url' => $this->productImage($product['name'], $category->image_url),
                    'material' => $product['material'], 'color' => $product['color'], 'stock' => 18 + (($index * 7) % 32),
                    'rating' => 4.6 + (($index % 4) / 10), 'review_count' => 18 + ($index * 13),
                    'is_featured' => $index < 2, 'is_best_seller' => $index % 2 === 0,
                ]);
            }
        });

        $this->seedDemoVolume($categoryModels, $productSets);
        $this->seedCommerceData();

        foreach (['Kitchen Ideas', 'Organization', 'Cleaning', 'Living Well'] as $name) {
            BlogCategory::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $posts = [
            ['title' => '10 Must-Have Kitchen Tools Every Home Needs', 'category' => 'Kitchen Ideas', 'image' => 'https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'The everyday tools that turn kitchen routines into a calmer, more enjoyable part of the day.'],
            ['title' => 'Smart Storage Ideas for Small Spaces', 'category' => 'Organization', 'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'A few gentle shifts can make a compact home feel remarkably spacious and easy to live in.'],
            ['title' => '5 Easy Ways to Refresh Your Living Room', 'category' => 'Living Well', 'image' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'Low-lift changes that bring more softness, texture and intention to your everyday rooms.'],
            ['title' => 'Eco-Friendly Cleaning Tips for Your Home', 'category' => 'Cleaning', 'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'Build a simpler cleaning ritual with reusable tools and a lighter footprint.'],
            ['title' => 'How to Choose the Right Cookware Set', 'category' => 'Kitchen Ideas', 'image' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'A practical guide to materials, shapes and the pieces you will reach for most.'],
            ['title' => 'Simple Ways to Keep Your Kitchen Clutter-Free', 'category' => 'Organization', 'image' => 'https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'Create visual breathing room with small, repeatable habits that actually stick.'],
            ['title' => 'Dining Table Setup Ideas for Family & Guests', 'category' => 'Living Well', 'image' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'Warm table settings for slow weekends, weeknight dinners and everything between.'],
            ['title' => 'How to Organize Your Bathroom', 'category' => 'Organization', 'image' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1000&q=85', 'excerpt' => 'Bring a little more ease to busy mornings with a clear, calm bathroom system.'],
        ];

        foreach ($posts as $index => $post) {
            $category = BlogCategory::where('name', $post['category'])->first();
            $slug = Str::slug($post['title']);
            BlogPost::updateOrCreate(['slug' => $slug], [
                'blog_category_id' => $category->id, 'title' => $post['title'], 'slug' => $slug,
                'excerpt' => $post['excerpt'], 'body' => '<p>' . e($post['excerpt']) . '</p><p>At HomeEase, we believe the most useful spaces are the ones that support real life. Start with one small change, give it a little time, and let the room evolve around the way you live.</p><h2>A softer approach to everyday routines</h2><p>Choose pieces that earn their place through thoughtful details and honest materials. The goal is not perfection; it is a home that feels good to come back to.</p>',
                'image_url' => $post['image'], 'author' => 'Maya from HomeEase', 'reading_time' => 4 + ($index % 4),
                'published_at' => now()->subDays($index + 2), 'is_published' => true,
                'meta_title' => $post['title'] . ' | HomeEase Journal', 'meta_description' => $post['excerpt'],
            ]);
        }
    }

    private function seedDemoVolume($categoryModels, array $productSets): void
    {
        $target = 120;
        $current = Product::count();
        $source = collect($productSets)->flatten(1)->values();
        $categories = $categoryModels->values();

        for ($i = $current; $i < $target; $i++) {
            $base = $source[$i % $source->count()];
            $category = $categories[$i % $categories->count()];
            $name = $base['name'] . ' ' . ['Edit', 'Everyday', 'Studio', 'Essential', 'Set'][$i % 5] . ' ' . ($i + 1);
            Product::create([
                'category_id' => $category->id, 'name' => $name, 'slug' => Str::slug($name),
                'sku' => 'HE-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'description' => 'Demo catalog item for the HomeEase Shop collection. Product details are illustrative until supplier information is connected.',
                'price' => $base['price'] + (($i % 6) * 3), 'compare_at_price' => $i % 3 === 0 ? $base['price'] + 12 : null,
                'image_url' => $this->productImage($base['name'], $category->image_url), 'material' => $base['material'], 'color' => $base['color'],
                'stock' => 10 + ($i % 40), 'rating' => 4.5 + (($i % 4) / 10), 'review_count' => 6 + ($i % 46),
                'is_featured' => false, 'is_best_seller' => $i % 7 === 0,
            ]);
        }
    }

    private function productImage(string $name, string $fallback): string
    {
        $images = [
            'Airtight Food Storage Set' => 'https://images.unsplash.com/photo-1573812914274-226dc19fbe17?auto=format&fit=crop&w=900&q=85',
            'Kitchen Utensil Holder' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=900&q=85',
            'Bamboo Cutting Board Set' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85',
            'Glass Oil Dispenser' => 'https://images.unsplash.com/photo-1552592074-ea7a91b851b3?auto=format&fit=crop&w=900&q=85',
            'Spice Jar Set with Rack' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=85',
            'Dish Drying Rack' => 'https://images.unsplash.com/photo-1780930425732-6fe2d9de072f?auto=format&fit=crop&w=900&q=85',
            'Vacuum Storage Bags' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=900&q=85',
            'Wall Mounted Organizer Shelf' => 'https://images.unsplash.com/photo-1597072689227-8882273e8f6a?auto=format&fit=crop&w=900&q=85',
            'Woven Catch-All Basket' => 'https://images.unsplash.com/photo-1593085260707-5377ba37f868?auto=format&fit=crop&w=900&q=85',
            'Stackable Pantry Bins' => 'https://images.unsplash.com/photo-1593085260707-5377ba37f868?auto=format&fit=crop&w=900&q=85',
            '360° Spin Mop & Bucket Set' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=85',
            'Microfiber Cleaning Cloths' => 'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=900&q=85',
            'Natural Dish Brush Set' => 'https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=900&q=85',
            'Refillable Spray Bottle' => 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?auto=format&fit=crop&w=900&q=85',
            'Bathroom Accessories Set' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=900&q=85',
            'Stoneware Soap Dispenser' => 'https://images.unsplash.com/photo-1695624825876-7110a459a21a?auto=format&fit=crop&w=900&q=85',
            'Over-Door Towel Rack' => 'https://images.unsplash.com/photo-1620626011761-996317b8d101?auto=format&fit=crop&w=900&q=85',
            'Linen Hand Towel Pair' => 'https://images.unsplash.com/photo-1684156752598-dc58f01ac3bb?auto=format&fit=crop&w=900&q=85',
            'Textured Throw Blanket' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=900&q=85',
            'Sculptural Table Lamp' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=900&q=85',
            'Soft Bouclé Cushion' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=900&q=85',
            'Oak Side Table' => 'https://images.unsplash.com/photo-1532372320572-cda25653a26d?auto=format&fit=crop&w=900&q=85',
            'Stoneware Serving Bowl' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=900&q=85',
            'Linen Napkin Set' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=900&q=85',
            'Acacia Serving Board' => 'https://images.unsplash.com/photo-1530018607912-eff2daa1bac4?auto=format&fit=crop&w=900&q=85',
            'Handmade Ceramic Vase' => 'https://images.unsplash.com/photo-1612196808214-b8e1d6145a8c?auto=format&fit=crop&w=900&q=85',
            'Scented Soy Candle' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=900&q=85',
            'Pleated Linen Lampshade' => 'https://images.unsplash.com/photo-1540932239986-30128078f3c5?auto=format&fit=crop&w=900&q=85',
            'Compact Air Purifier' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=900&q=85',
            'Quiet Electric Kettle' => 'https://images.unsplash.com/photo-1725419281142-3f89b330b907?auto=format&fit=crop&w=900&q=85',
            'Handheld Garment Steamer' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=900&q=85',
        ];

        foreach ($images as $label => $url) {
            if (Str::startsWith($name, $label)) return $url;
        }

        return $fallback;
    }

    private function seedCommerceData(): void
    {
        $brands = collect(['HomeEase Studio', 'Willow & Pine', 'Sunday Supply', 'Everyday Form'])->mapWithKeys(function (string $name) {
            $brand = Brand::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => 'Demo catalog brand pending supplier verification.', 'is_active' => true]);
            return [$name => $brand];
        });

        Product::query()->orderBy('id')->get()->each(function (Product $product, int $index) use ($brands) {
            $product->update(['brand_id' => $product->brand_id ?: $brands->values()[$index % $brands->count()]->id, 'is_published' => true, 'published_at' => $product->published_at ?: now()]);
            ProductImage::updateOrCreate(['product_id' => $product->id, 'sort_order' => 0], ['url' => $product->image_url, 'alt_text' => $product->name, 'is_primary' => true]);
            Inventory::updateOrCreate(['product_id' => $product->id, 'variant_id' => null], ['quantity' => $product->stock, 'reserved' => 0, 'reorder_level' => 5]);
        });

        $variantProduct = Product::where('slug', 'airtight-food-storage-set')->first();
        if ($variantProduct) {
            $variant = ProductVariant::updateOrCreate(['sku' => 'HE-KIT-001-L'], ['product_id' => $variantProduct->id, 'name' => 'Large · 4 piece', 'price' => 42, 'compare_at_price' => 49, 'options' => ['size' => 'Large', 'pieces' => 4], 'stock' => 12, 'is_active' => true]);
            Inventory::updateOrCreate(['product_id' => $variantProduct->id, 'variant_id' => $variant->id], ['quantity' => 12, 'reserved' => 0, 'reorder_level' => 3]);
        }

        Coupon::updateOrCreate(['code' => 'WELCOME10'], ['type' => 'percent', 'value' => 10, 'minimum_subtotal' => 25, 'max_uses' => null, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'SPRING30'], ['type' => 'percent', 'value' => 30, 'minimum_subtotal' => 80, 'max_uses' => 1000, 'is_active' => true]);
        StoreSetting::updateOrCreate(['key' => 'store.identity'], ['value' => ['name' => 'HomeEase Shop', 'tagline' => 'Make home life easier', 'currency' => 'USD']]);
        StoreSetting::updateOrCreate(['key' => 'store.shipping'], ['value' => ['free_threshold' => 49, 'standard_rate' => 6.99]]);
    }
}
