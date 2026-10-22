<?php

namespace App\Actions;

use App\Services\CurrencyService;

class FormatPriceAction
{
    public function __construct(
        protected CurrencyService $currencyService
    ) {}

    /**
     * Format a price according to shop currency rules.
     */
    public function execute(float|int $amount): string
    {
        return $this->currencyService->format($amount);
    }
}
