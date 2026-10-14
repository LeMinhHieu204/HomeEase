@extends('layouts.app')

@section('content')
<div class="container breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Journal</span></div>
<section class="blog-intro"><div class="container"><span class="eyebrow">Notes from HomeEase</span><h1>Our journal</h1><p>Ideas, rituals and thoughtful inspiration for a home that feels good to live in.</p></div></section>
<section class="blog-section"><div class="container blog-layout"><div class="blog-main"><div class="blog-grid blog-grid--three">
    @forelse($posts as $post)
        @include('partials.journal-card', compact('post'))
    @empty
        <div class="empty-state"><h2>No journal notes found</h2><p>Try another search.</p></div>
    @endforelse
</div><div class="pagination">{{ $posts->links() }}</div></div><aside class="blog-sidebar"><form action="{{ route('blog') }}" method="GET" class="sidebar-search"><input name="q" value="{{ request('q') }}" placeholder="Search the journal"><button type="submit" aria-label="Search journal">⌕</button></form><div class="sidebar-card"><h3>Explore topics</h3>@foreach($categories as $category)<a href="{{ route('blog', ['category' => $category->id]) }}">{{ $category->name }} <span>{{ $category->posts_count }}</span></a>@endforeach</div><div class="sidebar-card sidebar-card--soft"><span class="eyebrow">A note from us</span><h3>Make a little room for good things.</h3><p>Join the list for new journal notes and seasonal home inspiration.</p><a class="text-link" href="#newsletter">Sign me up <span>↗</span></a></div><div class="sidebar-card"><h3>Popular notes</h3>@foreach($popular as $popularPost)<a class="popular-post" href="{{ route('blog.show', $popularPost) }}"><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $popularPost->title }}</strong></a>@endforeach</div></aside></div></section>
@endsection
