<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'minimum_subtotal', 'max_uses', 'uses_count', 'starts_at', 'ends_at', 'is_active'];
    protected $casts = ['value' => 'decimal:2', 'minimum_subtotal' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    public function redemptions(): HasMany { return $this->hasMany(CouponRedemption::class); }
    public function isValidFor(float $subtotal): bool
    {
        return $this->is_active && $subtotal >= (float) $this->minimum_subtotal && (!$this->starts_at || now()->greaterThanOrEqualTo($this->starts_at)) && (!$this->ends_at || now()->lessThanOrEqualTo($this->ends_at)) && (!$this->max_uses || $this->uses_count < $this->max_uses);
    }
    public function discountFor(float $subtotal): float
    {
        return round(min($subtotal, $this->type === 'fixed' ? (float) $this->value : $subtotal * ((float) $this->value / 100)), 2);
    }
}
