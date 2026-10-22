<?php

namespace App\Filament\Resources\ExchangeRequests\Tables;

use App\Models\ExchangeRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExchangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (ExchangeRequest $record): ?string => $record->user?->phone),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40)
                    ->tooltip(fn (ExchangeRequest $record): string => $record->reason),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
