<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'button_text',
        'button_link',
        'image_path',
        'bg_gradient',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->hasMedia('banners')) {
            return $this->getFirstMediaUrl('banners');
        }

        if (empty($this->image_path)) {
            return asset('images/banners/hero-heels.webp');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        return asset('storage/' . $this->image_path);
    }

    public function getMobileImageUrlAttribute(): string
    {
        if ($this->hasMedia('mobile_banners')) {
            return $this->getFirstMediaUrl('mobile_banners');
        }

        $mobileCandidate = str_replace(['.webp', '-desktop.webp'], '-mobile.webp', $this->image_path ?? '');
        if ($mobileCandidate && file_exists(public_path($mobileCandidate))) {
            return asset($mobileCandidate);
        }
        if ($mobileCandidate && file_exists(public_path('storage/' . $mobileCandidate))) {
            return asset('storage/' . $mobileCandidate);
        }

        return $this->image_url;
    }
}
