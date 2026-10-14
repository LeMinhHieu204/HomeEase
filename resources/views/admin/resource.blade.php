@extends('layouts.app')

@section('content')
<section class="admin-shell">
    <div class="container">
        <div class="admin-header">
            <div><span class="eyebrow">HomeEase admin</span><h1>{{ $heading }}.</h1><p>{{ $description }}</p></div>
            @if($createRoute)<a class="button button--dark" href="{{ route($createRoute) }}">New record <span>↗</span></a>@endif
        </div>
        <div class="admin-layout">
            <aside class="admin-nav">
                <a href="{{ route('admin') }}">Overview</a><a href="{{ route('admin.products') }}">Products</a><a href="{{ route('admin.categories') }}">Categories</a><a href="{{ route('admin.brands') }}">Brands</a><a href="{{ route('admin.variants') }}">Variants</a><a href="{{ route('admin.inventory') }}">Inventory</a><a href="{{ route('admin.orders') }}">Orders</a><a href="{{ route('admin.customers') }}">Customers</a><a href="{{ route('admin.coupons') }}">Coupons</a><a href="{{ route('admin.blog') }}">Journal</a><a href="{{ route('admin.blog-categories') }}">Blog categories</a><a href="{{ route('admin.banners') }}">Banners</a><a href="{{ route('admin.media') }}">Media</a><a href="{{ route('admin.newsletter') }}">Newsletter</a><a href="{{ route('admin.settings') }}">Settings</a><a href="{{ route('admin.audit-logs') }}">Audit logs</a>
            </aside>
            <div class="admin-main"><div class="admin-panel admin-table"><div class="admin-panel__heading"><h2>{{ $heading }}</h2><span>{{ count($items) }} records</span></div><div class="admin-table__scroll"><table><thead><tr><th>Name / identifier</th><th>Details</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                @forelse($items as $item)
                    <tr><td><strong>{{ $item['primary'] }}</strong><small>{{ $item['secondary'] }}</small></td><td>{{ $item['details'] }}</td><td><span class="admin-status admin-status--green">{{ $item['status'] }}</span></td><td class="admin-actions">@if(!empty($item['edit']))<a class="admin-action" href="{{ $item['edit'] }}">Edit ↗</a>@endif @if(!empty($item['delete']))<form method="POST" action="{{ $item['delete'] }}" class="inline-admin-form">@csrf @method('DELETE')<button class="admin-action" type="submit">Disable</button></form>@endif</td></tr>
                @empty
                    <tr><td colspan="4">No records found.</td></tr>
                @endforelse
            </tbody></table></div></div></div>
        </div>
    </div>
</section>
@endsection
