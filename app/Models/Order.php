<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['user_id', 'number', 'status', 'payment_status', 'currency', 'subtotal', 'discount_total', 'shipping_total', 'tax_total', 'grand_total', 'email', 'shipping_method', 'coupon_code', 'shipping_address', 'billing_address', 'placed_at', 'payment_expires_at', 'inventory_released_at'];
    protected $casts = ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'shipping_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'shipping_address' => 'array', 'billing_address' => 'array', 'placed_at' => 'datetime', 'payment_expires_at' => 'datetime', 'inventory_released_at' => 'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payment(): HasOne { return $this->hasOne(Payment::class); }
    public function shipment(): HasOne { return $this->hasOne(Shipment::class); }
}
