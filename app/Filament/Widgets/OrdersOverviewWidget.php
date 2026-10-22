<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->sum('total');
        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStockVariants = ProductVariant::where('stock', '<=', 5)->count();

        return [
            Stat::make("Today's Orders", $todayOrders)
                ->description('Orders placed today')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make("Today's Revenue", '৳ ' . number_format($todayRevenue, 2))
                ->description('Total order value today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Confirmation', $pendingOrders)
                ->description('Orders awaiting dispatch approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),

            Stat::make('Low Stock Variants', $lowStockVariants)
                ->description('Variants with ≤ 5 pairs remaining')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockVariants > 0 ? 'danger' : 'success'),
        ];
    }
}
