<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'phone',
        'alternative_phone',
        'division',
        'district',
        'thana',
        'address_line',
        'postal_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->thana,
            $this->district,
            $this->division,
            $this->postal_code ? "Postal: {$this->postal_code}" : null,
        ]);

        return implode(', ', $parts);
    }
}
