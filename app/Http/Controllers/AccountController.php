<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('account/dashboard', ['user' => $request->user(), 'orders' => $request->user()->orders()->latest()->limit(5)->get()]);
    }

    public function profile(Request $request): View { return view('account/profile', ['user' => $request->user()]); }
    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id], 'phone' => ['nullable', 'string', 'max:30']]);
        $request->user()->update($data);
        return back()->with('success', 'Profile updated.');
    }

    public function addresses(Request $request): View { return view('account/addresses', ['addresses' => $request->user()->addresses()->latest()->get()]); }
    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:40'], 'recipient_name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:30'], 'line_1' => ['required', 'string', 'max:190'], 'line_2' => ['nullable', 'string', 'max:190'], 'city' => ['required', 'string', 'max:80'], 'state' => ['nullable', 'string', 'max:80'], 'postal_code' => ['required', 'string', 'max:30'], 'country' => ['required', 'string', 'size:2'], 'is_default' => ['nullable', 'boolean']]);
        if ($data['is_default'] ?? false) $request->user()->addresses()->update(['is_default' => false]);
        $request->user()->addresses()->create($data);
        return back()->with('success', 'Address saved.');
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();
        return back()->with('success', 'Address removed.');
    }

    public function orders(Request $request): View { return view('account/orders', ['orders' => $request->user()->orders()->with('items')->latest()->paginate(10)]); }
    public function order(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        return view('account/order', ['order' => $order->load(['items', 'payment', 'shipment'])]);
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required'], 'password' => ['required', 'confirmed', 'min:8']]);
        abort_unless(Hash::check($data['current_password'], $request->user()->password), 422, 'Current password is incorrect.');
        $request->user()->update(['password' => $data['password']]);
        return back()->with('success', 'Password changed.');
    }
}
