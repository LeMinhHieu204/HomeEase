<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View { return view('auth/login'); }
    public function showRegister(): View { return view('auth/register'); }

    public function register(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'confirmed', 'min:8']]);
        $guestSessionId = $request->session()->getId();
        $user = User::create([...$data, 'role' => 'customer', 'is_active' => true]);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $cart->mergeGuestCart($user, $guestSessionId);
        return redirect()->intended(route('account'))->with('success', 'Welcome to HomeEase, ' . $user->name . '.');
    }

    public function login(Request $request, CartService $cart): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'remember' => ['nullable', 'boolean']]);
        $guestSessionId = $request->session()->getId();
        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], (bool) ($credentials['remember'] ?? false))) {
            return back()->withErrors(['email' => 'Those credentials did not match our records.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        $cart->mergeGuestCart(Auth::user(), $guestSessionId);
        return redirect()->intended(route('account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'You have been signed out.');
    }

    public function showForgot(): View { return view('auth/forgot-password'); }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));
        return back()->with('success', __($status));
    }

    public function showReset(string $token): View { return view('auth/reset-password', ['token' => $token, 'email' => request('email')]); }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', 'min:8']]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
        });
        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('success', __($status)) : back()->withErrors(['email' => __($status)]);
    }
}
