<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $fillable = ['name', 'url', 'alt_text', 'collection', 'mime_type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
