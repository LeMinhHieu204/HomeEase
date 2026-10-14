<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'categories' => Category::query()->orderBy('id')->limit(5)->get(),
            'featuredProducts' => Product::with('category')->where('is_featured', true)->where('is_published', true)->latest()->limit(6)->get(),
            'bestSellers' => Product::with('category')->where('is_best_seller', true)->where('is_published', true)->latest()->limit(6)->get(),
            'posts' => BlogPost::with('category')->where('is_published', true)->whereNull('archived_at')->where(function ($q) { $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()); })->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function categoryBySlug(string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        return $this->category($category);
    }

    public function category(Category $category): View
    {
        $query = $category->products()->with('category')->where('is_published', true)->whereNull('archived_at');
        $search = request('q');
        $material = request('material');
        $sort = request('sort', 'featured');

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($material) $query->where('material', $material);
        if (request('sale')) $query->whereNotNull('compare_at_price');
        if (request('in_stock')) $query->where('stock', '>', 0);
        if (request('max_price')) $query->where('price', '<=', (float) request('max_price'));
        if ($sort === 'price-low') $query->orderBy('price');
        elseif ($sort === 'price-high') $query->orderByDesc('price');
        elseif ($sort === 'rating') $query->orderByDesc('rating');
        else $query->orderByDesc('is_featured')->latest();

        return view('catalog/category', [
            'category' => $category,
            'products' => $query->paginate(12)->withQueryString(),
            'materials' => $category->products()->whereNotNull('material')->distinct()->pluck('material'),
        ]);
    }

    public function shop(): View
    {
        $category = Category::firstOrCreate(['slug' => 'shop'], ['name' => 'Shop', 'description' => 'The full HomeEase collection.']);
        $products = Product::with('category')->where('is_published', true)->whereNull('archived_at')->when(request('in_stock'), fn ($query) => $query->where('stock', '>', 0))->when(request('sale'), fn ($query) => $query->whereNotNull('compare_at_price'))->when(request('max_price'), fn ($query) => $query->where('price', '<=', (float) request('max_price')))->latest()->paginate(12)->withQueryString();
        return view('catalog/category', ['category' => $category, 'products' => $products, 'materials' => Product::query()->distinct()->pluck('material')]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_published && !$product->archived_at, 404);
        return view('products/show', [
            'product' => $product->load('category'),
            'related' => Product::where('category_id', $product->category_id)->where('is_published', true)->where('id', '!=', $product->id)->limit(4)->get(),
        ]);
    }

    public function blog(): View
    {
        $term = trim((string) request('q', ''));
        $category = request('category');
        $postQuery = BlogPost::with('category')->where('is_published', true)->whereNull('archived_at')->where(function ($q) { $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()); });
        $postQuery->when($term, function ($query) use ($term) {
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%")->orWhere('body', 'like', "%{$term}%");
            });
        })->when($category, fn ($query) => $query->where('blog_category_id', $category));
        return view('blog/index', [
            'posts' => $postQuery->latest('published_at')->paginate(6)->withQueryString(),
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
            'popular' => BlogPost::where('is_published', true)->latest('published_at')->limit(4)->get(),
        ]);
    }

    public function blogShow(BlogPost $post): View
    {
        abort_unless($post->is_published && !$post->archived_at && (!$post->scheduled_at || $post->scheduled_at->isPast()), 404);
        return view('blog/show', ['post' => $post->load('category'), 'related' => BlogPost::where('id', '!=', $post->id)->where('is_published', true)->latest('published_at')->limit(3)->get()]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->input('q', ''));
        $products = Product::with('category')->where('is_published', true)->whereNull('archived_at')->when($term, function ($query) use ($term) {
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")->orWhere('material', 'like', "%{$term}%");
            });
        })->latest()->paginate(12)->withQueryString();
        return view('search', compact('term', 'products'));
    }

    public function cart(CartService $cart): View
    {
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        return view('cart/index', compact('items', 'subtotal'));
    }

    public function addToCart(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id'], 'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'], 'quantity' => ['nullable', 'integer', 'min:1', 'max:10']]);
        $product = Product::findOrFail($data['product_id']);
        $variant = !empty($data['variant_id']) ? $product->variants()->findOrFail($data['variant_id']) : null;
        $cart->add($product, (int) ($data['quantity'] ?? 1), $variant);
        return back()->with('success', 'Added to your basket.');
    }

    public function updateCart(Request $request, CartService $cart): RedirectResponse
    {
        $quantities = $request->validate(['quantities' => ['array']])['quantities'] ?? [];
        $cart->update($quantities);
        return back()->with('success', 'Basket updated.');
    }

    public function removeFromCart(string $id, CartService $cart): RedirectResponse
    {
        $cart->remove((int) $id);
        return back()->with('success', 'Item removed.');
    }

    public function toggleWishlist(Request $request, Product $product): RedirectResponse
    {
        $wishlist = collect($request->session()->get('wishlist', []));
        $message = $wishlist->contains($product->id) ? 'Removed from wishlist.' : 'Saved to wishlist.';
        $wishlist = $wishlist->contains($product->id) ? $wishlist->reject(fn ($id) => (int) $id === $product->id) : $wishlist->push($product->id);
        $request->session()->put('wishlist', $wishlist->values()->all());
        return back()->with('success', $message);
    }

    public function wishlist(): View
    {
        $products = Product::whereIn('id', session('wishlist', []))->get();
        return view('wishlist', compact('products'));
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        NewsletterSubscriber::updateOrCreate(['email' => strtolower($data['email'])], ['is_subscribed' => true]);
        return back()->with('success', 'You’re on the list. Welcome to the softer side of home.');
    }

    public function account(): View
    {
        return view('account');
    }

    public function admin(string $section = 'overview'): View
    {
        return view('admin/dashboard', [
            'section' => $section,
            'productsCount' => Product::count(),
            'postsCount' => BlogPost::count(),
            'subscribersCount' => NewsletterSubscriber::count(),
            'lowStock' => Product::where('stock', '<', 20)->orderBy('stock')->limit(5)->get(),
            'recentProducts' => Product::latest()->limit(5)->get(),
            'recentPosts' => BlogPost::latest('published_at')->limit(4)->get(),
        ]);
    }

    public function info(string $page): View
    {
        abort_unless(in_array($page, ['about', 'contact', 'faq', 'shipping-policy', 'returns-refunds', 'privacy-policy', 'terms']), 404);
        return view('info', ['page' => $page]);
    }

}
