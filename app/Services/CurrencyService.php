<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Format a numeric amount in Bangladeshi Taka (BDT).
     * Format example: "৳ 1,250.00"
     */
    public function format(float|int $amount): string
    {
        $symbol = config('shop.currency.symbol', '৳');
        $decimals = config('shop.currency.decimals', 2);
        $decPoint = config('shop.currency.decimal_separator', '.');
        $thousandsSep = config('shop.currency.thousand_separator', ',');
        $position = config('shop.currency.symbol_position', 'before');

        $formattedNumber = number_format($amount, $decimals, $decPoint, $thousandsSep);

        return $position === 'after'
            ? "{$formattedNumber} {$symbol}"
            : "{$symbol} {$formattedNumber}";
    }

    /**
     * Get the configured currency code (e.g. BDT).
     */
    public function code(): string
    {
        return config('shop.currency.code', 'BDT');
    }

    /**
     * Get the currency symbol (e.g. ৳).
     */
    public function symbol(): string
    {
        return config('shop.currency.symbol', '৳');
    }
}
