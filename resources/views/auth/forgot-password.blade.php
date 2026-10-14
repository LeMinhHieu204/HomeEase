@extends('layouts.app')

@section('content')
<section class="account-page"><div class="container account-card"><div class="account-card__intro"><span class="eyebrow">Account help</span><h1>Find your way<br><em>back home.</em></h1><p>Enter your email and we’ll send a secure password reset link when mail is configured.</p></div><form class="account-form" action="{{ route('password.email') }}" method="POST">@csrf<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label><button class="button button--dark button--full" type="submit">Send reset link <span>↗</span></button><p class="form-foot"><a href="{{ route('login') }}">Back to sign in</a></p></form></div></section>
@endsection
