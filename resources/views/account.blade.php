@extends('layouts.app')

@section('content')
<section class="account-page"><div class="container account-card"><div class="account-card__intro"><span class="eyebrow">Welcome back</span><h1>Your home,<br><em>all in one place.</em></h1><p>Sign in to follow orders, save favorites and make every future visit a little easier.</p></div><form class="account-form" action="#" method="POST" onsubmit="return false"><label>Email address<input type="email" placeholder="you@example.com"></label><label>Password<input type="password" placeholder="••••••••"></label><a href="#" class="form-link">Forgot password?</a><button class="button button--dark button--full" type="submit">Sign in <span>↗</span></button><p class="form-foot">New to HomeEase? <a href="#">Create an account</a></p></form></div></section>
@endsection
