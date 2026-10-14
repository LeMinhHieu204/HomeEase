@extends('layouts.app')

@section('content')
<section class="checkout-success"><div class="container"><span class="success-mark">✓</span><span class="eyebrow">Order received</span><h1>Thank you for making<br><em>home a little easier.</em></h1><p>Your order <strong>{{ $order->number }}</strong> is {{ str_replace('_', ' ', $order->status) }}. We’ve sent the details to {{ $order->email }}.</p><div class="success-card"><h2>Order summary</h2>@foreach($order->items as $item)<div><span>{{ $item->product_name }} × {{ $item->quantity }}</span><strong>${{ number_format($item->line_total, 2) }}</strong></div>@endforeach<div class="summary-total"><span>Total</span><strong>${{ number_format($order->grand_total, 2) }}</strong></div></div><a class="button button--dark" href="{{ auth()->check() ? route('account.orders') : route('shop') }}">{{ auth()->check() ? 'View your orders' : 'Continue shopping' }} <span>↗</span></a></div></section>
@endsection
