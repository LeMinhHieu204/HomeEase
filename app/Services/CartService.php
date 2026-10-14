<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function current(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['session_id' => null]);
        }

        if ($cartId = session('cart_id')) {
            return Cart::whereKey($cartId)->where('status', 'active')->firstOrFail();
        }
        $cart = Cart::firstOrCreate(['session_id' => session()->getId(), 'status' => 'active'], ['user_id' => null]);
        session(['cart_id' => $cart->id]);
        return $cart;
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): CartItem
    {
        return DB::transaction(function () use ($product, $quantity, $variant) {
            $inventoryQuery = Inventory::query()->lockForUpdate()->where('product_id', $product->id);
            $inventory = $variant ? $inventoryQuery->where('variant_id', $variant->id)->first() : $inventoryQuery->whereNull('variant_id')->first();
            $existing = $this->current()->items()->where('product_id', $product->id)->when($variant, fn ($q) => $q->where('variant_id', $variant->id), fn ($q) => $q->whereNull('variant_id'))->first();
            $requested = ($existing?->quantity ?? 0) + $quantity;
            $available = $inventory?->available ?? ($variant?->stock ?? $product->stock);

            if ($requested > $available) {
                throw ValidationException::withMessages(['quantity' => "Only {$available} of {$product->name} are available."]);
            }

            if ($existing) {
                $existing->update(['quantity' => $requested]);
                return $existing->fresh(['product', 'variant']);
            }

            return $this->current()->items()->create(['product_id' => $product->id, 'variant_id' => $variant?->id, 'quantity' => $quantity]);
        });
    }

    public function update(array $quantities): void
    {
        DB::transaction(function () use ($quantities) {
            $cart = $this->current();
            foreach ($quantities as $itemId => $quantity) {
                $item = $cart->items()->with(['product', 'variant'])->find($itemId);
                if (!$item) continue;
                if ((int) $quantity <= 0) { $item->delete(); continue; }
                $available = Inventory::query()->where('product_id', $item->product_id)->when($item->variant_id, fn ($q) => $q->where('variant_id', $item->variant_id), fn ($q) => $q->whereNull('variant_id'))->first()?->available ?? ($item->variant?->stock ?? $item->product->stock);
                if ((int) $quantity > $available) throw ValidationException::withMessages(['quantities.' . $itemId => "Only {$available} of {$item->product->name} are available."]);
                $item->update(['quantity' => (int) $quantity]);
            }
        });
    }

    public function remove(int $itemId): void { $this->current()->items()->whereKey($itemId)->delete(); }
    public function clear(): void { $this->current()->items()->delete(); }

    public function items()
    {
        return $this->current()->items()->with(['product.category', 'variant'])->get()->each(function (CartItem $item) {
            $item->cart_price = (float) ($item->variant?->price ?? $item->product->price);
            $item->cart_line_total = $item->cart_price * $item->quantity;
        });
    }

    public function subtotal(): float { return round((float) $this->items()->sum('cart_line_total'), 2); }

    public function mergeGuestCart(User $user, ?string $sessionId = null): void
    {
        $guestCartId = session('cart_id');
        DB::transaction(function () use ($user, $sessionId, $guestCartId) {
            $guest = Cart::when($guestCartId, fn ($q) => $q->whereKey($guestCartId), fn ($q) => $q->where('session_id', $sessionId))->where('status', 'active')->with('items')->first();
            if (!$guest) return;
            $customer = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active'], ['session_id' => null]);
            foreach ($guest->items as $guestItem) {
                $existing = $customer->items()->where('product_id', $guestItem->product_id)->when($guestItem->variant_id, fn ($q) => $q->where('variant_id', $guestItem->variant_id), fn ($q) => $q->whereNull('variant_id'))->first();
                if ($existing) $existing->increment('quantity', $guestItem->quantity);
                else $customer->items()->create($guestItem->only(['product_id', 'variant_id', 'quantity']));
            }
            $guest->update(['status' => 'merged']);
            $guest->items()->delete();
        });
        session()->forget('cart_id');
    }
}
