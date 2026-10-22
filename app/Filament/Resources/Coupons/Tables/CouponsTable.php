<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Promo Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->color('primary'),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'info' => 'percentage',
                        'success' => 'fixed',
                    ]),
                TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(fn (Coupon $record): string => $record->type === 'percentage' ? "{$record->value}%" : "৳ " . number_format($record->value, 2))
                    ->sortable(),
                TextColumn::make('usage')
                    ->label('Usage (Used / Limit)')
                    ->getStateUsing(fn (Coupon $record): string => "{$record->used_count} / " . ($record->usage_limit ?? '∞'))
                    ->badge()
                    ->color('slate'),
                TextColumn::make('min_spend')
                    ->label('Min Spend')
                    ->money('BDT')
                    ->placeholder('None'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->placeholder('Never')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
