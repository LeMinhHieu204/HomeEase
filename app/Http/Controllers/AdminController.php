<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function products(): View { return view('admin/products/index', ['products' => Product::with(['category', 'brand'])->latest()->paginate(20)]); }
    public function productCreate(): View { return view('admin/products/form', ['product' => new Product(), 'categories' => Category::orderBy('name')->get(), 'brands' => Brand::orderBy('name')->get()]); }
    public function productStore(Request $request): RedirectResponse
    {
        $data = $this->productData($request);
        $product = Product::create([...$data, 'slug' => Str::slug($data['name']), 'sku' => $data['sku'] ?: 'HE-' . Str::upper(Str::random(8)), 'published_at' => !empty($data['is_published']) ? now() : null]);
        Inventory::create(['product_id' => $product->id, 'quantity' => $data['stock'], 'reserved' => 0, 'reorder_level' => 5]);
        ProductImage::create(['product_id' => $product->id, 'url' => $data['image_url'], 'alt_text' => $data['name'], 'is_primary' => true]);
        $this->audit($request, 'product.created', $product);
        return redirect()->route('admin.products')->with('success', 'Product created and published state saved.');
    }
    public function productEdit(Product $product): View { return view('admin/products/form', ['product' => $product, 'categories' => Category::orderBy('name')->get(), 'brands' => Brand::orderBy('name')->get()]); }
    public function productUpdate(Request $request, Product $product): RedirectResponse
    {
        $data = $this->productData($request);
        $product->update([...$data, 'slug' => Str::slug($data['name']), 'published_at' => !empty($data['is_published']) ? ($product->published_at ?: now()) : null, 'archived_at' => !empty($data['is_published']) ? null : $product->archived_at]);
        $product->update(['stock' => $data['stock']]);
        $product->inventory()->updateOrCreate([], ['quantity' => $data['stock']]);
        $product->images()->updateOrCreate(['sort_order' => 0], ['url' => $data['image_url'], 'alt_text' => $data['name'], 'is_primary' => true]);
        $this->audit($request, 'product.updated', $product);
        return redirect()->route('admin.products')->with('success', 'Product updated.');
    }
    public function productArchive(Request $request, Product $product): RedirectResponse
    {
        $product->update(['is_published' => false, 'archived_at' => now()]);
        $this->audit($request, 'product.archived', $product);
        return back()->with('success', 'Product archived from storefront.');
    }

    public function inventory(): View { return view('admin/inventory/index', ['inventories' => Inventory::with(['product', 'variant'])->latest()->paginate(20)]); }
    public function inventoryUpdate(Request $request, Inventory $inventory): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0'], 'reorder_level' => ['required', 'integer', 'min:0']]);
        $inventory->update($data);
        if (!$inventory->variant_id) $inventory->product()->update(['stock' => $data['quantity']]);
        $this->audit($request, 'inventory.updated', $inventory);
        return back()->with('success', 'Inventory updated.');
    }

    public function orders(): View { return view('admin/orders/index', ['orders' => Order::with('user')->latest()->paginate(20)]); }
    public function orderUpdate(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:pending,awaiting_payment,processing,shipped,delivered,cancelled,refunded']]);
        $order->update(['status' => $data['status']]);
        if ($data['status'] === 'shipped') $order->shipment()->updateOrCreate([], ['status' => 'shipped', 'shipped_at' => now()]);
        if ($data['status'] === 'delivered') $order->shipment()->updateOrCreate([], ['status' => 'delivered', 'delivered_at' => now()]);
        $this->audit($request, 'order.status_updated', $order);
        return back()->with('success', 'Order status updated.');
    }

    public function blogPosts(): View { return view('admin/blog/index', ['posts' => BlogPost::with('category')->latest('created_at')->paginate(20)]); }
    public function blogCreate(): View { return view('admin/blog/form', ['post' => new BlogPost(), 'categories' => BlogCategory::orderBy('name')->get()]); }
    public function blogStore(Request $request): RedirectResponse
    {
        $data = $this->blogData($request);
        $post = BlogPost::create([...$data, 'slug' => Str::slug($data['title']), 'published_at' => !empty($data['is_published']) ? now() : null]);
        $this->audit($request, 'blog.created', $post);
        return redirect()->route('admin.blog')->with('success', 'Journal post created.');
    }
    public function blogEdit(BlogPost $post): View { return view('admin/blog/form', ['post' => $post, 'categories' => BlogCategory::orderBy('name')->get()]); }
    public function blogUpdate(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->blogData($request);
        $post->update([...$data, 'slug' => Str::slug($data['title']), 'published_at' => !empty($data['is_published']) ? ($post->published_at ?: now()) : null]);
        $this->audit($request, 'blog.updated', $post);
        return redirect()->route('admin.blog')->with('success', 'Journal post updated.');
    }
    public function blogArchive(Request $request, BlogPost $post): RedirectResponse
    {
        $post->update(['is_published' => false, 'archived_at' => now()]);
        $this->audit($request, 'blog.archived', $post);
        return back()->with('success', 'Journal post archived.');
    }

    public function categories(): View { return $this->resourceList('Categories', 'Organize the storefront catalog.', Category::withCount('products')->orderBy('name')->get()->map(fn (Category $item) => ['primary' => $item->name, 'secondary' => $item->slug, 'details' => $item->products_count . ' products', 'status' => 'Active', 'edit' => route('admin.categories.edit', $item), 'delete' => route('admin.categories.delete', $item)])->all(), 'admin.categories.create', 'admin.categories'); }
    public function categoryCreate(): View { return $this->resourceForm('New category', route('admin.categories.store'), 'POST', $this->categoryFields(new Category()), route('admin.categories')); }
    public function categoryStore(Request $request): RedirectResponse { $data = $this->categoryData($request); $category = Category::create([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'category.created', $category); return redirect()->route('admin.categories')->with('success', 'Category created.'); }
    public function categoryEdit(Category $category): View { return $this->resourceForm('Edit category', route('admin.categories.update', $category), 'PATCH', $this->categoryFields($category), route('admin.categories')); }
    public function categoryUpdate(Request $request, Category $category): RedirectResponse { $data = $this->categoryData($request); $category->update([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'category.updated', $category); return redirect()->route('admin.categories')->with('success', 'Category updated.'); }
    public function categoryDelete(Request $request, Category $category): RedirectResponse { if ($category->products()->exists()) return back()->with('error', 'Move or archive the products before deleting this category.'); $category->delete(); $this->audit($request, 'category.deleted', $category); return back()->with('success', 'Category deleted.'); }

    public function brands(): View { return $this->resourceList('Brands', 'Manage the brands shown in product records.', Brand::withCount('products')->orderBy('name')->get()->map(fn (Brand $item) => ['primary' => $item->name, 'secondary' => $item->slug, 'details' => $item->products_count . ' products', 'status' => $item->is_active ? 'Active' : 'Inactive', 'edit' => route('admin.brands.edit', $item), 'delete' => route('admin.brands.delete', $item)])->all(), 'admin.brands.create', 'admin.brands'); }
    public function brandCreate(): View { return $this->resourceForm('New brand', route('admin.brands.store'), 'POST', $this->brandFields(new Brand()), route('admin.brands')); }
    public function brandStore(Request $request): RedirectResponse { $data = $this->brandData($request); $brand = Brand::create([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'brand.created', $brand); return redirect()->route('admin.brands')->with('success', 'Brand created.'); }
    public function brandEdit(Brand $brand): View { return $this->resourceForm('Edit brand', route('admin.brands.update', $brand), 'PATCH', $this->brandFields($brand), route('admin.brands')); }
    public function brandUpdate(Request $request, Brand $brand): RedirectResponse { $data = $this->brandData($request); $brand->update([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'brand.updated', $brand); return redirect()->route('admin.brands')->with('success', 'Brand updated.'); }
    public function brandDelete(Request $request, Brand $brand): RedirectResponse { if ($brand->products()->exists()) return back()->with('error', 'Move the products before deleting this brand.'); $brand->delete(); $this->audit($request, 'brand.deleted', $brand); return back()->with('success', 'Brand deleted.'); }

    public function blogCategories(): View { return $this->resourceList('Blog categories', 'Organize Journal content.', BlogCategory::withCount('posts')->orderBy('name')->get()->map(fn (BlogCategory $item) => ['primary' => $item->name, 'secondary' => $item->slug, 'details' => $item->posts_count . ' posts', 'status' => 'Active', 'edit' => route('admin.blog-categories.edit', $item), 'delete' => route('admin.blog-categories.delete', $item)])->all(), 'admin.blog-categories.create', 'admin.blog-categories'); }
    public function blogCategoryCreate(): View { return $this->resourceForm('New blog category', route('admin.blog-categories.store'), 'POST', $this->blogCategoryFields(new BlogCategory()), route('admin.blog-categories')); }
    public function blogCategoryStore(Request $request): RedirectResponse { $data = $request->validate(['name' => ['required', 'string', 'max:120']]); $category = BlogCategory::create([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'blog_category.created', $category); return redirect()->route('admin.blog-categories')->with('success', 'Blog category created.'); }
    public function blogCategoryEdit(BlogCategory $category): View { return $this->resourceForm('Edit blog category', route('admin.blog-categories.update', $category), 'PATCH', $this->blogCategoryFields($category), route('admin.blog-categories')); }
    public function blogCategoryUpdate(Request $request, BlogCategory $category): RedirectResponse { $data = $request->validate(['name' => ['required', 'string', 'max:120']]); $category->update([...$data, 'slug' => Str::slug($data['name'])]); $this->audit($request, 'blog_category.updated', $category); return redirect()->route('admin.blog-categories')->with('success', 'Blog category updated.'); }
    public function blogCategoryDelete(Request $request, BlogCategory $category): RedirectResponse { if ($category->posts()->exists()) return back()->with('error', 'Move the posts before deleting this category.'); $category->delete(); $this->audit($request, 'blog_category.deleted', $category); return back()->with('success', 'Blog category deleted.'); }

    public function newsletter(): View { return view('admin/simple-list', ['heading' => 'Newsletter', 'items' => NewsletterSubscriber::latest()->get(), 'label' => 'subscribers', 'adminSection' => 'newsletter']); }

    public function customers(): View
    {
        $items = User::latest()->get()->map(fn (User $item) => ['primary' => $item->name, 'secondary' => $item->email, 'details' => ucfirst(str_replace('_', ' ', $item->role)), 'status' => $item->is_active ? 'Active' : 'Blocked', 'edit' => route('admin.customers.edit', $item)])->all();
        return $this->resourceList('Customers', 'Review customer accounts and server-side access state.', $items, null, 'admin.customers');
    }
    public function customerEdit(User $customer): View { $fields = [['name' => 'is_active', 'label' => 'Account active', 'type' => 'checkbox', 'value' => $customer->is_active]]; if (request()->user()?->isSuperAdmin()) $fields[] = ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'value' => $customer->role, 'options' => ['customer' => 'Customer', 'staff' => 'Staff', 'admin' => 'Admin', 'super_admin' => 'Super admin']]; return $this->resourceForm('Edit customer access', route('admin.customers.update', $customer), 'PATCH', $fields, route('admin.customers')); }
    public function customerUpdate(Request $request, User $customer): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['nullable', 'boolean'], 'role' => ['nullable', 'in:customer,staff,admin,super_admin']]);
        $updates = ['is_active' => (bool) ($data['is_active'] ?? false)];
        if ($request->user()->isSuperAdmin() && array_key_exists('role', $data) && $data['role']) $updates['role'] = $data['role'];
        $customer->update($updates);
        $this->audit($request, 'customer.access_updated', $customer);
        return redirect()->route('admin.customers')->with('success', 'Customer access updated.');
    }

    public function coupons(): View
    {
        $items = Coupon::latest()->get()->map(fn (Coupon $item) => ['primary' => $item->code, 'secondary' => strtoupper($item->type) . ' ' . $item->value, 'details' => 'Min $' . number_format($item->minimum_subtotal, 2), 'status' => $item->is_active ? 'Active' : 'Inactive', 'edit' => route('admin.coupons.edit', $item), 'delete' => route('admin.coupons.delete', $item)])->all();
        return $this->resourceList('Coupons', 'Control server-side promotions and usage limits.', $items, 'admin.coupons.create', 'admin.coupons');
    }
    public function couponCreate(): View { return $this->resourceForm('New coupon', route('admin.coupons.store'), 'POST', $this->couponFields(new Coupon()), route('admin.coupons')); }
    public function couponStore(Request $request): RedirectResponse { $coupon = Coupon::create($this->couponData($request)); $this->audit($request, 'coupon.created', $coupon); return redirect()->route('admin.coupons')->with('success', 'Coupon created.'); }
    public function couponEdit(Coupon $coupon): View { return $this->resourceForm('Edit coupon', route('admin.coupons.update', $coupon), 'PATCH', $this->couponFields($coupon), route('admin.coupons')); }
    public function couponUpdate(Request $request, Coupon $coupon): RedirectResponse { $coupon->update($this->couponData($request)); $this->audit($request, 'coupon.updated', $coupon); return redirect()->route('admin.coupons')->with('success', 'Coupon updated.'); }
    public function couponDelete(Request $request, Coupon $coupon): RedirectResponse { $coupon->update(['is_active' => false]); $this->audit($request, 'coupon.disabled', $coupon); return back()->with('success', 'Coupon disabled.'); }

    public function variants(): View
    {
        $items = ProductVariant::with('product')->latest()->get()->map(fn (ProductVariant $item) => ['primary' => $item->name, 'secondary' => $item->sku, 'details' => $item->product?->name . ' · ' . $item->stock . ' stock', 'status' => $item->is_active ? 'Active' : 'Inactive', 'edit' => route('admin.variants.edit', $item), 'delete' => route('admin.variants.delete', $item)])->all();
        return $this->resourceList('Product variants', 'Manage variant pricing, options and stock.', $items, 'admin.variants.create', 'admin.variants');
    }
    public function variantCreate(): View { return $this->resourceForm('New product variant', route('admin.variants.store'), 'POST', $this->variantFields(new ProductVariant()), route('admin.variants')); }
    public function variantStore(Request $request): RedirectResponse { $variant = ProductVariant::create($this->variantData($request)); $this->syncVariantInventory($variant); $this->audit($request, 'variant.created', $variant); return redirect()->route('admin.variants')->with('success', 'Variant created.'); }
    public function variantEdit(ProductVariant $variant): View { return $this->resourceForm('Edit product variant', route('admin.variants.update', $variant), 'PATCH', $this->variantFields($variant), route('admin.variants')); }
    public function variantUpdate(Request $request, ProductVariant $variant): RedirectResponse { $variant->update($this->variantData($request)); $this->syncVariantInventory($variant); $this->audit($request, 'variant.updated', $variant); return redirect()->route('admin.variants')->with('success', 'Variant updated.'); }
    public function variantDelete(Request $request, ProductVariant $variant): RedirectResponse { $variant->update(['is_active' => false]); $this->audit($request, 'variant.disabled', $variant); return back()->with('success', 'Variant disabled.'); }

    public function productImages(Product $product): View { return view('admin/product-images', ['product' => $product->load('images'), 'images' => $product->images()->get()]); }
    public function productImageStore(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['url' => ['required', 'url', 'max:500'], 'alt_text' => ['nullable', 'string', 'max:190'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_primary' => ['nullable', 'boolean']]);
        if (!empty($data['is_primary'])) $product->images()->update(['is_primary' => false]);
        $image = $product->images()->create([...$data, 'is_primary' => !empty($data['is_primary'])]);
        $this->audit($request, 'product_image.created', $image);
        return back()->with('success', 'Product image added.');
    }
    public function productImageDelete(Request $request, ProductImage $image): RedirectResponse { $product = $image->product; $image->delete(); $this->audit($request, 'product_image.deleted', $image); return redirect()->route('admin.products.images', $product)->with('success', 'Product image removed.'); }

    public function banners(): View
    {
        $items = Banner::latest()->get()->map(fn (Banner $item) => ['primary' => $item->title, 'secondary' => $item->placement, 'details' => $item->image_url, 'status' => $item->is_active ? 'Active' : 'Inactive', 'edit' => route('admin.banners.edit', $item), 'delete' => route('admin.banners.delete', $item)])->all();
        return $this->resourceList('Banners', 'Manage database-backed promotional placements.', $items, 'admin.banners.create', 'admin.banners');
    }
    public function bannerCreate(): View { return $this->resourceForm('New banner', route('admin.banners.store'), 'POST', $this->bannerFields(new Banner()), route('admin.banners')); }
    public function bannerStore(Request $request): RedirectResponse { $banner = Banner::create($this->bannerData($request)); $this->audit($request, 'banner.created', $banner); return redirect()->route('admin.banners')->with('success', 'Banner created.'); }
    public function bannerEdit(Banner $banner): View { return $this->resourceForm('Edit banner', route('admin.banners.update', $banner), 'PATCH', $this->bannerFields($banner), route('admin.banners')); }
    public function bannerUpdate(Request $request, Banner $banner): RedirectResponse { $banner->update($this->bannerData($request)); $this->audit($request, 'banner.updated', $banner); return redirect()->route('admin.banners')->with('success', 'Banner updated.'); }
    public function bannerDelete(Request $request, Banner $banner): RedirectResponse { $banner->update(['is_active' => false]); $this->audit($request, 'banner.disabled', $banner); return back()->with('success', 'Banner disabled.'); }

    public function media(): View
    {
        $items = Media::latest()->get()->map(fn (Media $item) => ['primary' => $item->name, 'secondary' => $item->collection, 'details' => $item->url, 'status' => $item->is_active ? 'Active' : 'Inactive', 'edit' => route('admin.media.edit', $item), 'delete' => route('admin.media.delete', $item)])->all();
        return $this->resourceList('Media', 'Manage verified image URLs and alt text.', $items, 'admin.media.create', 'admin.media');
    }
    public function mediaCreate(): View { return $this->resourceForm('New media item', route('admin.media.store'), 'POST', $this->mediaFields(new Media()), route('admin.media')); }
    public function mediaStore(Request $request): RedirectResponse { $media = Media::create($this->mediaData($request)); $this->audit($request, 'media.created', $media); return redirect()->route('admin.media')->with('success', 'Media item created.'); }
    public function mediaEdit(Media $media): View { return $this->resourceForm('Edit media item', route('admin.media.update', $media), 'PATCH', $this->mediaFields($media), route('admin.media')); }
    public function mediaUpdate(Request $request, Media $media): RedirectResponse { $media->update($this->mediaData($request)); $this->audit($request, 'media.updated', $media); return redirect()->route('admin.media')->with('success', 'Media item updated.'); }
    public function mediaDelete(Request $request, Media $media): RedirectResponse { $media->update(['is_active' => false]); $this->audit($request, 'media.disabled', $media); return back()->with('success', 'Media item disabled.'); }

    public function settings(): View
    {
        $items = StoreSetting::orderBy('key')->get()->map(fn (StoreSetting $item) => ['primary' => $item->key, 'secondary' => json_encode($item->value), 'details' => 'Store setting', 'status' => 'Active', 'edit' => route('admin.settings.edit', $item)])->all();
        return $this->resourceList('Store settings', 'Edit operational settings stored in the database.', $items, 'admin.settings.create', 'admin.settings');
    }
    public function settingCreate(): View { return $this->resourceForm('New store setting', route('admin.settings.store'), 'POST', $this->settingFields(new StoreSetting()), route('admin.settings')); }
    public function settingStore(Request $request): RedirectResponse { $setting = StoreSetting::create($this->settingData($request)); $this->audit($request, 'store_setting.created', $setting); return redirect()->route('admin.settings')->with('success', 'Store setting created.'); }
    public function settingEdit(StoreSetting $setting): View { return $this->resourceForm('Edit store setting', route('admin.settings.update', $setting), 'PATCH', $this->settingFields($setting), route('admin.settings')); }
    public function settingUpdate(Request $request, StoreSetting $setting): RedirectResponse { $setting->update($this->settingData($request)); $this->audit($request, 'store_setting.updated', $setting); return redirect()->route('admin.settings')->with('success', 'Store setting updated.'); }

    public function auditLogs(): View
    {
        $items = AuditLog::with('user')->latest()->get()->map(fn (AuditLog $item) => ['primary' => $item->action, 'secondary' => $item->user?->email ?? 'System', 'details' => ($item->target_type ? class_basename($item->target_type) . '#' . $item->target_id : '') . ' · ' . $item->created_at?->format('M d, Y H:i'), 'status' => 'Recorded'])->all();
        return $this->resourceList('Audit logs', 'Immutable record of administrator writes.', $items, null, 'admin.audit-logs');
    }

    private function resourceList(string $heading, string $description, array $items, ?string $createRoute, string $adminSection): View
    {
        return view('admin/resource', compact('heading', 'description', 'items', 'createRoute', 'adminSection'));
    }

    private function resourceForm(string $heading, string $action, string $method, array $fields, string $back): View
    {
        return view('admin/resource-form', compact('heading', 'action', 'method', 'fields', 'back'));
    }

    private function categoryFields(Category $category): array { return [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $category->name], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'value' => $category->description], ['name' => 'image_url', 'label' => 'Image URL', 'type' => 'url', 'value' => $category->image_url]]; }
    private function brandFields(Brand $brand): array { return [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $brand->name], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'value' => $brand->description], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'value' => $brand->exists ? $brand->is_active : true]]; }
    private function blogCategoryFields(BlogCategory $category): array { return [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $category->name]]; }
    private function couponFields(Coupon $coupon): array { return [['name' => 'code', 'label' => 'Code', 'type' => 'text', 'value' => $coupon->code], ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'value' => $coupon->type ?: 'percent', 'options' => ['percent' => 'Percent', 'fixed' => 'Fixed amount']], ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'step' => '0.01', 'value' => $coupon->value], ['name' => 'minimum_subtotal', 'label' => 'Minimum subtotal', 'type' => 'number', 'step' => '0.01', 'value' => $coupon->minimum_subtotal], ['name' => 'max_uses', 'label' => 'Maximum uses', 'type' => 'number', 'value' => $coupon->max_uses], ['name' => 'starts_at', 'label' => 'Starts at', 'type' => 'datetime-local', 'value' => $coupon->starts_at?->format('Y-m-d\\TH:i')], ['name' => 'ends_at', 'label' => 'Ends at', 'type' => 'datetime-local', 'value' => $coupon->ends_at?->format('Y-m-d\\TH:i')], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'value' => $coupon->exists ? $coupon->is_active : true]]; }
    private function variantFields(ProductVariant $variant): array { return [['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'value' => $variant->product_id, 'options' => Product::orderBy('name')->pluck('name', 'id')->all()], ['name' => 'sku', 'label' => 'SKU', 'type' => 'text', 'value' => $variant->sku], ['name' => 'name', 'label' => 'Variant name', 'type' => 'text', 'value' => $variant->name], ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01', 'value' => $variant->price], ['name' => 'compare_at_price', 'label' => 'Compare at price', 'type' => 'number', 'step' => '0.01', 'value' => $variant->compare_at_price], ['name' => 'options', 'label' => 'Options JSON', 'type' => 'textarea', 'value' => $variant->options ? json_encode($variant->options, JSON_PRETTY_PRINT) : ''], ['name' => 'stock', 'label' => 'Stock', 'type' => 'number', 'value' => $variant->stock], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'value' => $variant->exists ? $variant->is_active : true]]; }
    private function bannerFields(Banner $banner): array { return [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'value' => $banner->title], ['name' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'value' => $banner->subtitle], ['name' => 'image_url', 'label' => 'Image URL', 'type' => 'url', 'value' => $banner->image_url], ['name' => 'link_url', 'label' => 'Link URL', 'type' => 'url', 'value' => $banner->link_url], ['name' => 'placement', 'label' => 'Placement', 'type' => 'text', 'value' => $banner->placement ?: 'home'], ['name' => 'starts_at', 'label' => 'Starts at', 'type' => 'datetime-local', 'value' => $banner->starts_at?->format('Y-m-d\\TH:i')], ['name' => 'ends_at', 'label' => 'Ends at', 'type' => 'datetime-local', 'value' => $banner->ends_at?->format('Y-m-d\\TH:i')], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'value' => $banner->exists ? $banner->is_active : true]]; }
    private function mediaFields(Media $media): array { return [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $media->name], ['name' => 'url', 'label' => 'URL', 'type' => 'url', 'value' => $media->url], ['name' => 'alt_text', 'label' => 'Alt text', 'type' => 'text', 'value' => $media->alt_text], ['name' => 'collection', 'label' => 'Collection', 'type' => 'text', 'value' => $media->collection ?: 'general'], ['name' => 'mime_type', 'label' => 'MIME type', 'type' => 'text', 'value' => $media->mime_type], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'value' => $media->exists ? $media->is_active : true]]; }
    private function settingFields(StoreSetting $setting): array { return [['name' => 'key', 'label' => 'Key', 'type' => 'text', 'value' => $setting->key], ['name' => 'value', 'label' => 'Value JSON', 'type' => 'textarea', 'value' => $setting->value ? json_encode($setting->value, JSON_PRETTY_PRINT) : '{}']]; }

    private function categoryData(Request $request): array { return $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string'], 'image_url' => ['nullable', 'url', 'max:500']]); }
    private function brandData(Request $request): array { $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean']]); $data['is_active'] = (bool) ($data['is_active'] ?? false); return $data; }
    private function couponData(Request $request): array { $data = $request->validate(['code' => ['required', 'string', 'max:50'], 'type' => ['required', 'in:percent,fixed'], 'value' => ['required', 'numeric', 'min:0'], 'minimum_subtotal' => ['required', 'numeric', 'min:0'], 'max_uses' => ['nullable', 'integer', 'min:1'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'is_active' => ['nullable', 'boolean']]); $data['code'] = Str::upper(trim($data['code'])); $data['is_active'] = (bool) ($data['is_active'] ?? false); return $data; }
    private function variantData(Request $request): array { $data = $request->validate(['product_id' => ['required', 'exists:products,id'], 'sku' => ['required', 'string', 'max:80'], 'name' => ['required', 'string', 'max:120'], 'price' => ['nullable', 'numeric', 'min:0'], 'compare_at_price' => ['nullable', 'numeric', 'gte:price'], 'options' => ['nullable', 'json'], 'stock' => ['required', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]); $data['options'] = $data['options'] ? json_decode($data['options'], true) : null; $data['is_active'] = (bool) ($data['is_active'] ?? false); return $data; }
    private function bannerData(Request $request): array { $data = $request->validate(['title' => ['required', 'string', 'max:190'], 'subtitle' => ['nullable', 'string'], 'image_url' => ['required', 'url', 'max:500'], 'link_url' => ['nullable', 'url', 'max:500'], 'placement' => ['required', 'string', 'max:50'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'is_active' => ['nullable', 'boolean']]); $data['is_active'] = (bool) ($data['is_active'] ?? false); return $data; }
    private function mediaData(Request $request): array { $data = $request->validate(['name' => ['required', 'string', 'max:190'], 'url' => ['required', 'url', 'max:500'], 'alt_text' => ['nullable', 'string', 'max:190'], 'collection' => ['required', 'string', 'max:80'], 'mime_type' => ['nullable', 'string', 'max:80'], 'is_active' => ['nullable', 'boolean']]); $data['is_active'] = (bool) ($data['is_active'] ?? false); return $data; }
    private function settingData(Request $request): array { $data = $request->validate(['key' => ['required', 'string', 'max:120'], 'value' => ['required', 'json']]); return ['key' => $data['key'], 'value' => json_decode($data['value'], true)]; }
    private function syncVariantInventory(ProductVariant $variant): void { Inventory::updateOrCreate(['product_id' => $variant->product_id, 'variant_id' => $variant->id], ['quantity' => $variant->stock, 'reserved' => $variant->inventory?->reserved ?? 0, 'reorder_level' => $variant->inventory?->reorder_level ?? 5]); }

    private function productData(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:190'], 'sku' => ['nullable', 'string', 'max:80'], 'category_id' => ['required', 'exists:categories,id'], 'brand_id' => ['nullable', 'exists:brands,id'], 'description' => ['required', 'string'], 'price' => ['required', 'numeric', 'min:0'], 'compare_at_price' => ['nullable', 'numeric', 'gte:price'], 'image_url' => ['required', 'url', 'max:500'], 'material' => ['nullable', 'string', 'max:80'], 'color' => ['nullable', 'string', 'max:80'], 'stock' => ['required', 'integer', 'min:0'], 'is_published' => ['nullable', 'boolean']]);
    }
    private function blogData(Request $request): array
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:190'], 'blog_category_id' => ['required', 'exists:blog_categories,id'], 'excerpt' => ['required', 'string'], 'body' => ['required', 'string'], 'image_url' => ['required', 'url', 'max:500'], 'author' => ['required', 'string', 'max:120'], 'reading_time' => ['required', 'integer', 'min:1', 'max:60'], 'meta_title' => ['nullable', 'string', 'max:190'], 'meta_description' => ['nullable', 'string', 'max:255'], 'is_published' => ['nullable', 'boolean'], 'scheduled_at' => ['nullable', 'date']]);
        $data['body'] = $this->sanitizeRichText($data['body']);
        return $data;
    }
    private function sanitizeRichText(string $html): string
    {
        $allowed = '<p><br><strong><em><h2><h3><ul><ol><li><a><blockquote>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?: '';
        return preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $clean) ?: $clean;
    }
    private function audit(Request $request, string $action, object $target): void { AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'target_type' => $target::class, 'target_id' => $target->id, 'metadata' => ['after' => $target->getAttributes()], 'ip_address' => $request->ip()]); }
}
