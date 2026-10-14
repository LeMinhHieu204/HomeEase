@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="container hero__frame">
        <div class="hero__copy"><span class="eyebrow">Thoughtful goods for everyday living</span><h1>Simplify your home,<br><em>enjoy every day.</em></h1><p>Smart products for a cleaner, more organized and more comfortable home.</p><a class="button button--dark" href="{{ route('shop') }}">Shop the collection <span>↗</span></a><div class="hero__dots" aria-label="Hero slides"><span class="is-active"></span><span></span><span></span></div></div>
        <div class="hero__image"><img src="https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1600&q=88" alt="Warm, organized modern kitchen with natural wood details"></div>
    </div>
</section>

<section class="section categories-section"><div class="container"><div class="section-heading"><div><span class="eyebrow">Find your everyday</span><h2>Shop by room</h2></div><a class="text-link" href="{{ route('shop') }}">View all categories <span>↗</span></a></div><div class="category-grid">@foreach($categories as $category)<a class="category-tile" href="{{ route('categories.show', $category) }}"><div class="category-tile__image"><img src="{{ $category->image_url }}" alt="{{ $category->name }}"></div><span>{{ $category->name }}</span><small>Explore <b>↗</b></small></a>@endforeach</div></div></section>

<section class="section products-section products-section--tint"><div class="container"><div class="section-heading"><div><span class="eyebrow">Good things, chosen well</span><h2>Featured favorites</h2></div><a class="text-link" href="{{ route('shop') }}">Shop all <span>↗</span></a></div><div class="product-grid">@foreach($featuredProducts as $product) @include('partials.product-card', compact('product')) @endforeach</div></div></section>

<section class="section promo-section"><div class="container promo-card"><div class="promo-card__image"><img src="https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=1200&q=85" alt="Linen, basket and greenery styled in a bright home"></div><div class="promo-card__copy"><span class="eyebrow">Just in</span><h2>New arrivals<br><em>for fresh starts.</em></h2><p>Discover the latest must-haves for a home that works beautifully for you.</p><a class="button button--dark" href="{{ route('new-arrivals') }}">Shop new in <span>↗</span></a><span class="promo-card__sale">Up to 30% off</span></div></div></section>

<section class="benefits"><div class="container benefits__grid">@include('partials.icon-row', ['icon' => '✦', 'title' => 'Free shipping', 'copy' => 'On orders over $49'])@include('partials.icon-row', ['icon' => '↺', 'title' => 'Easy returns', 'copy' => '30-day return window'])@include('partials.icon-row', ['icon' => '⌁', 'title' => 'Secure payment', 'copy' => 'Safe & simple checkout'])@include('partials.icon-row', ['icon' => '♡', 'title' => 'Chosen with care', 'copy' => 'Quality for real homes'])</div></section>

<section class="section products-section"><div class="container"><div class="section-heading"><div><span class="eyebrow">Most loved</span><h2>Best sellers</h2></div><a class="text-link" href="{{ route('best-sellers') }}">See best sellers <span>↗</span></a></div><div class="product-grid">@foreach($bestSellers as $product) @include('partials.product-card', compact('product')) @endforeach</div></div></section>

<section class="section philosophy-section"><div class="container"><div class="philosophy-intro"><span class="eyebrow">Why HomeEase</span><h2>Small details.<br><em>A softer everyday.</em></h2><p>We believe the objects around you should earn their place. That's why every HomeEase find is chosen for the way it looks, feels and quietly makes life easier.</p><a class="text-link" href="{{ url('/about') }}">More about us <span>↗</span></a></div><div class="philosophy-grid"><div><span>01</span><h3>Designed for real homes</h3><p>Practical, beautiful pieces that meet you where life actually happens.</p></div><div><span>02</span><h3>Less, but better</h3><p>Considered materials and useful details over unnecessary excess.</p></div><div><span>03</span><h3>Here for you</h3><p>A warm, helpful experience from first click to your front door.</p></div></div></div></section>

<section class="section journal-preview"><div class="container"><div class="section-heading"><div><span class="eyebrow">The HomeEase Journal</span><h2>Ideas for living well</h2></div><a class="text-link" href="{{ route('blog') }}">Read the journal <span>↗</span></a></div><div class="journal-grid">@foreach($posts as $post) @include('partials.journal-card', compact('post')) @endforeach</div></div></section>
@endsection
