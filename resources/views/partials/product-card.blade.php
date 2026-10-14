<article class="product-card">
    <div class="product-card__visual">
        <a href="{{ route('products.show', $product) }}" class="product-card__image-wrap"><img src="{{ $product->image_url }}" alt="{{ $product->name }}"></a>
        @if($product->compare_at_price)<span class="sale-tag">Sale</span>@endif
        <form action="{{ route('wishlist.toggle', $product) }}" method="POST" class="wishlist-form">@csrf<button type="submit" aria-label="Save {{ $product->name }}">♡</button></form>
    </div>
    <div class="product-card__body">
        <a class="product-card__category" href="{{ route('categories.show', $product->category) }}">{{ $product->category?->name }}</a>
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="product-card__price-row"><strong>${{ number_format($product->price, 2) }}</strong>@if($product->compare_at_price)<del>${{ number_format($product->compare_at_price, 2) }}</del>@endif</div>
        <div class="product-card__rating"><span class="stars">★★★★★</span><span class="reviews">({{ $product->review_count }})</span></div>
        <form action="{{ route('cart.add') }}" method="POST" class="quick-add">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><button type="submit">Add to cart <span>↗</span></button></form>
    </div>
</article>
