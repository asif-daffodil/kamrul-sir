<?php

namespace App\Filament\Resources\Outlets\Tables;

use App\Models\Outlet;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OutletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Showroom Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('district')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Dhaka' ? 'rose' : 'indigo')
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('opening_hours')
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('district')
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Chittagong' => 'Chittagong',
                        'Sylhet' => 'Sylhet',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_map')
                    ->label('Map')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->color('slate')
                    ->url(fn (Outlet $record): ?string => $record->map_link)
                    ->openUrlInNewTab()
                    ->visible(fn (Outlet $record): bool => ! empty($record->map_link)),
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
