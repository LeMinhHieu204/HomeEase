<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'description', 'price', 'compare_at_price',
        'image_url', 'material', 'color', 'stock', 'rating', 'review_count', 'is_featured', 'is_best_seller', 'is_published', 'published_at', 'archived_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function inventory(): HasOne { return $this->hasOne(Inventory::class)->whereNull('variant_id'); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
