<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id', 'title', 'slug', 'excerpt', 'body', 'image_url', 'author',
        'reading_time', 'published_at', 'scheduled_at', 'archived_at', 'is_published', 'meta_title', 'meta_description',
    ];

    protected $casts = ['published_at' => 'datetime', 'scheduled_at' => 'datetime', 'archived_at' => 'datetime', 'is_published' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
