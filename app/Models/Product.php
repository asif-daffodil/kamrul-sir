<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'base_price',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getMinPriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) ($this->variants->min('price') ?? $this->base_price);
        }

        return (float) $this->base_price;
    }

    public function getMaxPriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) ($this->variants->max('price') ?? $this->base_price);
        }

        return (float) $this->base_price;
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $minSale = $this->variants->whereNotNull('sale_price')->min('sale_price');
            if ($minSale !== null) {
                return (float) $minSale;
            }
            return (float) ($this->variants->min('price') ?? $this->base_price);
        }

        return (float) $this->base_price;
    }

    public function getOriginalPriceAttribute(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) ($this->variants->max('price') ?? $this->base_price);
        }

        return (float) $this->base_price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->effective_price < $this->original_price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->has_discount || $this->original_price <= 0) {
            return 0;
        }

        return (int) round((($this->original_price - $this->effective_price) / $this->original_price) * 100);
    }

    public function getAvailableSizesAttribute(): array
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->where('stock', '>', 0)->pluck('size')->unique()->sort()->values()->toArray();
        }

        return [];
    }

    public function getAvailableColorsAttribute(): array
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->where('stock', '>', 0)->map(fn ($v) => [
                'name' => $v->color,
                'code' => $v->color_code,
            ])->unique('name')->values()->toArray();
        }

        return [];
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('approvedReviews') && $this->approvedReviews->isNotEmpty()) {
            return round($this->approvedReviews->avg('rating'), 1);
        }

        return 4.9;
    }

    public function getReviewsCountAttribute(): int
    {
        if ($this->relationLoaded('approvedReviews')) {
            return $this->approvedReviews->count();
        }

        return 0;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->sum('stock');
        }

        return 0;
    }

    protected static array $resolvedAssetPaths = [];

    protected function resolveAssetPath(string $path): string
    {
        if (isset(static::$resolvedAssetPaths[$path])) {
            return static::$resolvedAssetPaths[$path];
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return static::$resolvedAssetPaths[$path] = $path;
        }

        if (file_exists(public_path($path))) {
            return static::$resolvedAssetPaths[$path] = asset($path);
        }

        if (file_exists(public_path('storage/' . $path))) {
            return static::$resolvedAssetPaths[$path] = asset('storage/' . $path);
        }

        return static::$resolvedAssetPaths[$path] = asset($path);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->hasMedia('products')) {
            return $this->getFirstMediaUrl('products');
        }

        if ($this->relationLoaded('primaryImage') && $this->primaryImage && ! empty($this->primaryImage->image_path)) {
            return $this->resolveAssetPath($this->primaryImage->image_path);
        }

        return asset('images/shoes/placeholder-shoe.webp');
    }

    public function getSecondaryImageUrlAttribute(): string
    {
        if ($this->hasMedia('products') && $this->getMedia('products')->count() > 1) {
            return $this->getMedia('products')->get(1)->getUrl();
        }

        if ($this->relationLoaded('images') && $this->images->count() > 1) {
            $second = $this->images->skip(1)->first();
            if ($second && ! empty($second->image_path)) {
                return $this->resolveAssetPath($second->image_path);
            }
        }

        $sidePath = "products/{$this->slug}-side.webp";
        if (isset(static::$resolvedAssetPaths[$sidePath]) || file_exists(public_path($sidePath))) {
            return $this->resolveAssetPath($sidePath);
        }

        return $this->image_url;
    }
}
