@extends('layouts.app')

@php($content = [
    'about' => ['Our story', 'A home should make room for the life you want to live.', 'HomeEase is a considered collection of useful, beautiful goods for everyday spaces. We look for the little details that make routines smoother, rooms calmer and coming home feel good.'],
    'contact' => ['Contact us', 'We’re here to make things easier.', 'Have a question about an order, a product or choosing the right piece? Send us a note and our small, friendly team will get back to you within two business days.'],
    'faq' => ['Frequently asked questions', 'The helpful bits, all in one place.', 'How long does shipping take? Most orders arrive within 3–5 business days. Can I return an item? Yes, within 30 days of delivery, in original condition. Is checkout secure? Yes — payments are processed through secure providers and card details are never stored by HomeEase.'],
    'shipping-policy' => ['Shipping & returns', 'A straightforward experience from us to you.', 'We offer free shipping on orders over $49. For orders below that, a small flat rate is shown at checkout. If something is not quite right, contact us within 30 days and we’ll help with the next step.'],
    'returns-refunds' => ['Returns & refunds', 'If it’s not right for your home, we’ll help.', 'Items can be returned within 30 days of delivery in unused, original condition. Once received, refunds are issued to the original payment method. This demo store does not process real payments.'],
    'privacy-policy' => ['Privacy policy', 'Your trust matters to us.', 'HomeEase only collects the information needed to provide a useful shopping experience, process orders and send updates when you opt in. We do not sell personal information.'],
    'terms' => ['Terms & conditions', 'The fine print, kept human.', 'This demo storefront is a production-oriented prototype. Product information, prices and availability are illustrative until connected to verified supplier and payment infrastructure.'],
][$page] ?? ['HomeEase', 'Make home life easier.', 'Thoughtful goods for everyday living.']);

@section('content')
<div class="container breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>{{ $content[0] }}</span></div><section class="info-page"><div class="container info-page__inner"><span class="eyebrow">HomeEase</span><h1>{{ $content[0] }}</h1><h2>{{ $content[1] }}</h2><p>{{ $content[2] }}</p><div class="info-page__rule"></div><a class="text-link" href="{{ route('home') }}">Back home <span>↗</span></a></div></section>
@endsection
