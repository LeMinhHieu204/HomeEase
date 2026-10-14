@extends('layouts.app')

@section('content')
<div class="container breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Wishlist</span></div><section class="wishlist-page"><div class="container"><span class="eyebrow">Saved for later</span><h1>Your wishlist</h1>@if($products->isEmpty())<div class="empty-state empty-state--large"><span>♡</span><h2>Nothing saved yet</h2><p>Tap the heart on any piece you love and find it here.</p><a class="button button--dark" href="{{ route('shop') }}">Explore the collection <span>↗</span></a></div>@else<div class="product-grid product-grid--catalog">@foreach($products as $product) @include('partials.product-card', compact('product')) @endforeach</div>@endif</div></section>
@endsection
