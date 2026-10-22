<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('N/A'),
                TextColumn::make('email')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'staff',
                        'info' => 'customer',
                    ])
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders')
                    ->badge()
                    ->color('rose')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'customer' => 'Customers',
                        'staff' => 'Staff',
                        'admin' => 'Administrators',
                    ]),
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
