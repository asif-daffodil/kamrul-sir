<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Retrieve a setting value by key, with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'decimal', 'float' => (float) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * Update or create a setting key-value pair and clear cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $type = null): self
    {
        $computedType = $type ?? match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'decimal',
            is_array($value) => 'json',
            default => 'string',
        };

        $stringValue = is_array($value) ? json_encode($value) : (string) $value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'group' => $group,
                'type' => $computedType,
            ]
        );

        Cache::forget("setting.{$key}");
        Cache::forget('settings_all_group');
        Cache::forget('storefront_home_data');

        return $setting;
    }

    /**
     * Retrieve all settings grouped by key from cache.
     */
    public static function getAllCached(): array
    {
        return Cache::remember('settings_all_group', 3600, function () {
            return static::all()->mapWithKeys(function ($setting) {
                $val = match ($setting->type) {
                    'integer' => (int) $setting->value,
                    'decimal', 'float' => (float) $setting->value,
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };
                return [$setting->key => $val];
            })->toArray();
        });
    }

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings_all_group');
            Cache::forget('storefront_home_data');
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings_all_group');
            Cache::forget('storefront_home_data');
        });
    }
}
