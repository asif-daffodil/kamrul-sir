<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alert (≤ 5 Pairs Remaining)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => ProductVariant::query()
                    ->with('product')
                    ->where('stock', '<=', 5)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('color')
                    ->badge()
                    ->color('slate'),
                TextColumn::make('size')
                    ->label('Size (EU)')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sku')
                    ->fontFamily('mono')
                    ->color('gray'),
                TextColumn::make('stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'OUT OF STOCK' : "{$state} left")
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('BDT'),
            ])
            ->paginated([5, 10])
            ->recordActions([
                Action::make('edit_product')
                    ->label('Restock')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('primary')
                    ->url(fn (ProductVariant $record): string => ProductResource::getUrl('edit', ['record' => $record->product_id])),
            ]);
    }
}
