<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->customer_phone),
                TextColumn::make('shipping_district')
                    ->label('Destination')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Dhaka' ? 'emerald' : 'indigo')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total (BDT)')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ]),
                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'primary' => 'processing',
                        'cyan' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'gray' => 'returned',
                    ]),
                TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'returned' => 'Returned',
                    ]),
                SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('shipping_district')
                    ->label('District')
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Chittagong' => 'Chittagong',
                        'Sylhet' => 'Sylhet',
                        'Gazipur' => 'Gazipur',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('print_invoice')
                    ->label('Invoice')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->color('slate')
                    ->url(fn (Order $record) => route('admin.orders.invoice', $record))
                    ->openUrlInNewTab(),
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
