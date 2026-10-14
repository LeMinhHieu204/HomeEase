@extends('layouts.app')

@section('content')
<div class="container breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Search</span></div>
<section class="search-page"><div class="container"><span class="eyebrow">Search HomeEase</span><h1>{{ $term ? 'Results for “'.$term.'”' : 'Find your next everyday essential' }}</h1><form class="search-page__form" action="{{ route('search') }}"><input type="search" name="q" value="{{ $term }}" placeholder="Try “storage”, “bamboo” or “kitchen”"><button class="button button--dark" type="submit">Search <span>↗</span></button></form>@if($term && $products->count())<p class="search-page__count">{{ $products->total() }} pieces found</p><div class="product-grid product-grid--catalog">@foreach($products as $product) @include('partials.product-card', compact('product')) @endforeach</div><div class="pagination">{{ $products->links() }}</div>@elseif($term)<div class="empty-state"><span>⌕</span><h2>Nothing came up</h2><p>Try a broader search or browse our rooms.</p><a class="button button--dark" href="{{ route('shop') }}">Browse all pieces</a></div>@endif</div></section>
@endsection
