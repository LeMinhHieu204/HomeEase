<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    protected $fillable = ['payment_id', 'provider', 'event_id', 'type', 'payload', 'processed_at'];
    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
