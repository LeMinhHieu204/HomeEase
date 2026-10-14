<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'HomeEase Shop — considered home essentials for a calmer, more comfortable everyday.' }}">
    <title>{{ $title ?? 'HomeEase Shop — Make Home Life Easier' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="announcement">
        <div class="container announcement__inner"><span>Spring sale: up to 30% off select items</span><span class="announcement__desktop">Free shipping on orders over $49 <span class="announcement__dot">•</span> Shop now</span></div>
    </div>
    <header class="site-header">
        <div class="container header-main">
            <button class="icon-button mobile-only" type="button" data-menu-toggle aria-label="Open menu">☰</button>
            <a class="brand" href="{{ route('home') }}" aria-label="HomeEase Shop home">
                <span class="brand-mark"><svg viewBox="0 0 44 44" aria-hidden="true"><path d="M8 21.4 22 9l14 12.4v13.2a2.7 2.7 0 0 1-2.7 2.7H10.7A2.7 2.7 0 0 1 8 34.6V21.4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M16.5 36.8V25h11v11.8M17 17.5c2.1-3.9 8-3.9 10 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
                <span><strong>HomeEase</strong><small>Make home life easier</small></span>
            </a>
            <form class="header-search" action="{{ route('search') }}" method="GET">
                <input name="q" value="{{ request('q') }}" placeholder="Search for products..." aria-label="Search for products">
                <button type="submit" aria-label="Submit search">⌕</button>
            </form>
            <div class="header-actions">
                <a href="{{ route('account') }}" class="header-action desktop-only"><span class="action-icon">♙</span><span>Account</span></a>
                <a href="{{ route('wishlist') }}" class="header-action desktop-only"><span class="action-icon">♡</span><span>Wishlist</span></a>
                @php($cartQuantity = app(\App\Services\CartService::class)->items()->sum('quantity'))
                <a href="{{ route('cart') }}" class="header-action cart-action"><span class="action-icon">▱</span><span class="desktop-only">Cart</span><b class="cart-count">{{ $cartQuantity }}</b></a>
            </div>
        </div>
        <nav class="nav" data-mobile-menu>
            <div class="container nav__inner">
                <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="{{ request()->is('kitchen') ? 'is-active' : '' }}" href="{{ route('kitchen') }}">Kitchen</a>
                <a class="{{ request()->is('storage') ? 'is-active' : '' }}" href="{{ route('storage') }}">Storage</a>
                <a class="{{ request()->is('cleaning') ? 'is-active' : '' }}" href="{{ route('cleaning') }}">Cleaning</a>
                <a class="{{ request()->is('bathroom') ? 'is-active' : '' }}" href="{{ route('bathroom') }}">Bathroom</a>
                <a class="{{ request()->is('living') ? 'is-active' : '' }}" href="{{ route('living') }}">Living</a>
                <a href="{{ route('deals') }}">Deals <span class="nav-pill">New</span></a>
                <a href="{{ route('new-arrivals') }}">New arrivals</a>
                <a class="{{ request()->routeIs('blog*') ? 'is-active' : '' }}" href="{{ route('blog') }}">Journal</a>
                <a href="{{ url('/contact') }}">Contact</a>
            </div>
        </nav>
    </header>

    @if(session('success'))
        <div class="toast" role="status">{{ session('success') }} <button type="button" data-dismiss-toast aria-label="Dismiss">×</button></div>
    @endif
    @if($errors->any())
        <div class="toast toast--error" role="alert">{{ $errors->first() }} <button type="button" data-dismiss-toast aria-label="Dismiss">×</button></div>
    @endif

    <main>@yield('content')</main>

    @if($showNewsletterBand ?? false)
        <section class="newsletter-band">
            <div class="container newsletter-band__inner">
                <div><span class="eyebrow eyebrow--light">A little more ease, delivered</span><h2>Make space for<br><em>good things.</em></h2></div>
                <div class="newsletter-copy"><p>Get thoughtful ideas for your home, new collection notes and a warm welcome offer.</p><form action="{{ route('newsletter.subscribe') }}" method="POST">@csrf<input type="email" name="email" placeholder="Your email address" required><button class="button button--cream" type="submit">Join the list <span>↗</span></button></form><small>By subscribing, you agree to receive our occasional notes. Unsubscribe anytime.</small></div>
            </div>
        </section>
    @endif

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand"><a class="brand brand--footer" href="{{ route('home') }}"><span class="brand-mark">⌂</span><span><strong>HomeEase</strong><small>Make home life easier</small></span></a><p>Considered goods for a home that feels good to live in.</p><div class="socials"><a href="#" aria-label="Instagram">ig</a><a href="#" aria-label="Pinterest">p</a><a href="#" aria-label="Facebook">f</a></div></div>
            <div><h3>Shop</h3><a href="{{ route('kitchen') }}">Kitchen</a><a href="{{ route('storage') }}">Storage</a><a href="{{ route('cleaning') }}">Cleaning</a><a href="{{ route('living') }}">Living</a><a href="{{ route('deals') }}">Sale</a></div>
            <div><h3>Customer care</h3><a href="{{ url('/contact') }}">Contact us</a><a href="{{ url('/shipping-policy') }}">Shipping & returns</a><a href="{{ url('/faq') }}">FAQs</a><a href="{{ route('account') }}">My account</a><a href="{{ route('wishlist') }}">Wishlist</a></div>
            <div><h3>Stay updated</h3><p class="footer-copy">Join for seasonal notes, useful ideas and a warm welcome offer.</p><form class="footer-newsletter" action="{{ route('newsletter.subscribe') }}" method="POST">@csrf<input type="email" name="email" placeholder="Your email address" required><button type="submit">Subscribe</button></form><a href="{{ url('/about') }}">Our story</a><a href="{{ route('blog') }}">The Journal</a><a href="{{ url('/privacy-policy') }}">Privacy</a><a href="{{ url('/terms') }}">Terms</a><span class="footer-note">Thoughtfully chosen. Made for real life.</span></div>
        </div>
        <div class="container footer-bottom"><span>© {{ date('Y') }} HomeEase Shop. All rights reserved.</span><span>Secure checkout · Visa · Mastercard · PayPal</span></div>
    </footer>
</body>
</html>
