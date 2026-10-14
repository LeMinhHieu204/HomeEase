<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'name', 'price', 'compare_at_price', 'options', 'stock', 'is_active'];
    protected $casts = ['price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'options' => 'array', 'is_active' => 'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function inventory(): HasOne { return $this->hasOne(Inventory::class, 'variant_id'); }
}
