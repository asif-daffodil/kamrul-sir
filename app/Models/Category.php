<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public const NAV_CACHE_KEY = 'storefront_nav_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Retrieve root categories with children and counts from cache.
     */
    public static function getCachedNavHierarchy()
    {
        return \Illuminate\Support\Facades\Cache::remember(self::NAV_CACHE_KEY, 86400, function () {
            return static::roots()
                ->active()
                ->with(['children' => fn ($q) => $q->active()])
                ->withCount(['products' => fn ($q) => $q->active()])
                ->orderBy('sort_order')
                ->get();
        });
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget(self::NAV_CACHE_KEY);
            \Illuminate\Support\Facades\Cache::forget('storefront_home_data');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget(self::NAV_CACHE_KEY);
            \Illuminate\Support\Facades\Cache::forget('storefront_home_data');
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image)) {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            if (file_exists(public_path($this->image))) {
                return asset($this->image);
            }
            if (file_exists(public_path('storage/' . $this->image))) {
                return asset('storage/' . $this->image);
            }
        }

        return asset('images/categories/' . $this->slug . '.webp');
    }
}
