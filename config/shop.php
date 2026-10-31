<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop Currency & Locale Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bangladeshi Taka (BDT) and shop defaults.
    |
    */

    'currency' => [
        'code' => env('APP_CURRENCY', 'BDT'),
        'symbol' => env('APP_CURRENCY_SYMBOL', '৳'),
        'name' => env('APP_CURRENCY_NAME', 'Bangladeshi Taka'),
        'decimals' => (int) env('APP_CURRENCY_DECIMALS', 2),
        'thousand_separator' => env('APP_CURRENCY_THOUSAND_SEPARATOR', ','),
        'decimal_separator' => env('APP_CURRENCY_DECIMAL_SEPARATOR', '.'),
        'symbol_position' => env('APP_CURRENCY_SYMBOL_POSITION', 'before'), // 'before' (৳ 1,250) or 'after' (1,250 ৳)
    ],

    'store' => [
        'name' => env('APP_BRAND_NAME', 'Heel Bari'),
        'country' => 'Bangladesh',
        'default_locale' => env('APP_LOCALE', 'en'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Dhaka'),
    ],

];
