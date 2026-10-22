<?php

use Livewire\Component;
use App\Services\CurrencyService;

new class extends Component
{
    public int $quantity = 1;
    public float $unitPrice = 2450.00; // Authentic server-side price (e.g. Leather Heels)

    public function increment(): void
    {
        if ($this->quantity < 10) {
            $this->quantity++;
        }
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function getTotalProperty(CurrencyService $currency): string
    {
        // Enforcing Rule: Recalculate 100% on the server
        return $currency->format($this->quantity * $this->unitPrice);
    }

    public function getFormattedUnitProperty(CurrencyService $currency): string
    {
        return $currency->format($this->unitPrice);
    }
};
?>

<div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50/50 via-white to-pink-50/30 p-5 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                Server-Side Price Calculation
            </span>
            <h3 class="mt-2 text-base font-semibold text-gray-900">Velvet Block Heel Pump</h3>
            <p class="text-xs text-gray-500">Unit Price: <span class="font-medium text-gray-800">{{ $this->formattedUnit }}</span></p>
        </div>
        <div class="text-right">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Total (BDT)</div>
            <div class="text-xl font-bold tracking-tight text-rose-600">{{ $this->total }}</div>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-between border-t border-rose-100/70 pt-4">
        <span class="text-xs font-medium text-gray-600">Select Pairs:</span>
        <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white shadow-xs">
            <button
                type="button"
                wire:click="decrement"
                class="px-3 py-1.5 text-sm font-bold text-gray-600 hover:bg-gray-50 disabled:opacity-30 transition rounded-l-lg"
                @if($quantity <= 1) disabled @endif
            >
                −
            </button>
            <span class="px-4 py-1.5 text-sm font-semibold text-gray-800 tabular-nums">
                {{ $quantity }}
            </span>
            <button
                type="button"
                wire:click="increment"
                class="px-3 py-1.5 text-sm font-bold text-gray-600 hover:bg-gray-50 disabled:opacity-30 transition rounded-r-lg"
                @if($quantity >= 10) disabled @endif
            >
                +
            </button>
        </div>
    </div>
</div>