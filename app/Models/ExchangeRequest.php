<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'requested_variant_id',
        'reason',
        'status',
        'customer_notes',
        'admin_notes',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'requested_variant_id');
    }
}
