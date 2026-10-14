<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'subtitle', 'image_url', 'link_url', 'placement', 'is_active', 'starts_at', 'ends_at'];
    protected $casts = ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
}
