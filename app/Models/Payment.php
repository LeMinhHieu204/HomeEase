<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['order_id', 'provider', 'provider_payment_id', 'status', 'amount', 'currency', 'idempotency_key', 'raw_response', 'paid_at'];
    protected $casts = ['amount' => 'decimal:2', 'raw_response' => 'array', 'paid_at' => 'datetime'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function events(): HasMany { return $this->hasMany(PaymentEvent::class); }
}
