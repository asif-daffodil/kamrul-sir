<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'shipping_address_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_division',
        'shipping_district',
        'shipping_thana',
        'shipping_address',
        'subtotal',
        'discount_amount',
        'delivery_charge',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'coupon_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function exchangeRequests(): HasMany
    {
        return $this->hasMany(ExchangeRequest::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeExchanged(): bool
    {
        return $this->status === 'delivered';
    }
}
