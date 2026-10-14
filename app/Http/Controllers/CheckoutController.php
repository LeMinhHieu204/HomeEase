<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, CartService $cart): View|RedirectResponse
    {
        if ($cart->items()->isEmpty()) return redirect()->route('cart')->withErrors(['cart' => 'Add something to your basket before checkout.']);
        return view('checkout/index', ['items' => $cart->items(), 'subtotal' => $cart->subtotal(), 'user' => $request->user()]);
    }

    public function place(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255'], 'recipient_name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:30'], 'line_1' => ['required', 'string', 'max:190'], 'line_2' => ['nullable', 'string', 'max:190'], 'city' => ['required', 'string', 'max:80'], 'state' => ['nullable', 'string', 'max:80'], 'postal_code' => ['required', 'string', 'max:30'], 'country' => ['required', 'string', 'size:2'], 'shipping_method' => ['required', 'in:standard,express'], 'coupon_code' => ['nullable', 'string', 'max:40']]);
        $data['shipping_address'] = collect($data)->only(['recipient_name', 'phone', 'line_1', 'line_2', 'city', 'state', 'postal_code', 'country'])->all();
        try {
            $order = $orders->place($data, $request->user());
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['checkout' => app()->environment('testing') ? $exception->getMessage() : 'We could not confirm payment. Check your details or try again.']);
        }
        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, string $order): View
    {
        $model = \App\Models\Order::where('number', $order)->firstOrFail();
        if ($model->user_id && $model->user_id !== $request->user()?->id) abort(403);
        return view('checkout/success', ['order' => $model->load('items')]);
    }
}
