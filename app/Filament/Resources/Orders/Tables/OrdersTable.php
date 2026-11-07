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
                TextColumn::make('courier_name')
                    ->label('Courier')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : 'Unassigned')
                    ->color(fn (?string $state) => match ($state) {
                        'steadfast' => 'info',
                        'pathao' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('courier_tracking_code')
                    ->label('Tracking Code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Tracking code copied')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('courier_status')
                    ->label('Courier Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'gray' => 'returned',
                    ])
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->searchable()
                    ->preload()
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'returned' => 'Returned',
                    ]),
                SelectFilter::make('courier_name')
                    ->label('Courier')
                    ->searchable()
                    ->preload()
                    ->options([
                        'steadfast' => 'Steadfast Courier',
                        'pathao' => 'Pathao Courier',
                    ]),
                SelectFilter::make('payment_status')
                    ->searchable()
                    ->preload()
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('shipping_district')
                    ->label('District')
                    ->searchable()
                    ->preload()
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Chittagong' => 'Chittagong',
                        'Sylhet' => 'Sylhet',
                        'Gazipur' => 'Gazipur',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('send_to_courier')
                    ->label('Send to Courier')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('primary')
                    ->modalHeading(fn (Order $record) => 'Book Courier Consignment for #' . $record->order_number)
                    ->modalDescription('Create parcel consignment with Bangladeshi courier and generate tracking number.')
                    ->visible(fn (Order $record) => empty($record->courier_tracking_code) && ! in_array($record->status, ['cancelled', 'delivered']))
                    ->form([
                        \Filament\Forms\Components\Select::make('courier')
                            ->label('Select Courier Service')
                            ->options([
                                'steadfast' => 'Steadfast Courier (BD Nationwide)',
                                'pathao' => 'Pathao Courier (Express / Standard)',
                            ])
                            ->default(config('courier.default', 'steadfast'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        \Filament\Forms\Components\TextInput::make('note')
                            ->label('Delivery Instructions / Note')
                            ->default(fn (Order $record) => $record->notes ?: 'Women Footwear - Handle with care')
                            ->helperText('Courier rider instructions (e.g. Call before delivery)'),
                    ])
                    ->action(function (Order $record, array $data) {
                        /** @var \App\Services\Courier\CourierManager $manager */
                        $manager = app(\App\Services\Courier\CourierManager::class);

                        $result = $manager->sendOrderToCourier(
                            order: $record,
                            driverName: $data['courier'],
                            options: ['note' => $data['note'] ?? null]
                        );

                        if ($result->successful) {
                            $record->refresh();
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Consignment created with ' . ucfirst($data['courier']))
                                ->body("Tracking Code: {$record->courier_tracking_code}")
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Courier Booking Failed')
                                ->body($result->message)
                                ->persistent()
                                ->send();
                        }
                    }),
                Action::make('sync_courier_status')
                    ->label('Sync Status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('slate')
                    ->visible(fn (Order $record) => ! empty($record->courier_tracking_code))
                    ->action(function (Order $record) {
                        \App\Jobs\SyncCourierDeliveryStatusJob::dispatchSync($record->id);

                        $record->refresh();
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('Status Synced')
                            ->body("Courier Status: " . ucfirst($record->courier_status ?? 'Unknown'))
                            ->send();
                    }),
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
