@extends('layouts.app')

@section('content')
<section class="account-page"><div class="container account-card"><div class="account-card__intro"><span class="eyebrow">Set a new password</span><h1>A fresh start<br><em>for your account.</em></h1><p>Choose a strong password with at least eight characters.</p></div><form class="account-form" action="{{ route('password.update') }}" method="POST">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Email address<input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"></label><label>New password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label><button class="button button--dark button--full" type="submit">Update password <span>↗</span></button></form></div></section>
@endsection
