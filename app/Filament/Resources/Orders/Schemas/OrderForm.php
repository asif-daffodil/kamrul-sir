<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Order Status & Lifecycle')
                    ->description('Manage order workflow and transition states')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending Confirmation',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing / Packaging',
                                'shipped' => 'Shipped / Handed to Courier',
                                'delivered' => 'Delivered to Customer',
                                'cancelled' => 'Cancelled',
                                'returned' => 'Returned / Returned by Courier',
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('payment_method')
                            ->label('Payment Channel')
                            ->options([
                                'cod' => 'Cash On Delivery (COD)',
                                'bkash' => 'bKash Online Payment',
                                'nagad' => 'Nagad Online Payment',
                                'sslcommerz' => 'SSLCommerz Gateway',
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(['default' => 1, 'lg' => 2]),
                        TextInput::make('coupon_code')
                            ->label('Applied Coupon')
                            ->placeholder('No coupon applied')
                            ->disabled()
                            ->columnSpan(['default' => 1, 'lg' => 1]),
                        TextInput::make('status_comment')
                            ->label('Log Note for Status Change')
                            ->placeholder('e.g. Courier tracking ID: STEADFAST-998812')
                            ->helperText('Logged into order audit trail on save')
                            ->columnSpanFull(),
                    ]),

                Section::make('Courier & Logistics Delivery')
                    ->description('Steadfast and Pathao consignment details and live courier status')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'md' => 3, 'lg' => 5])
                    ->collapsible()
                    ->schema([
                        TextInput::make('courier_name')
                            ->label('Courier Service')
                            ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : 'Not Dispatched')
                            ->disabled(),
                        TextInput::make('courier_tracking_code')
                            ->label('Tracking Code')
                            ->placeholder('No tracking code')
                            ->disabled(),
                        TextInput::make('courier_consignment_id')
                            ->label('Consignment ID')
                            ->placeholder('No consignment ID')
                            ->disabled(),
                        TextInput::make('courier_status')
                            ->label('Courier Status')
                            ->placeholder('Not assigned')
                            ->disabled(),
                        TextInput::make('courier_sent_at')
                            ->label('Dispatched At')
                            ->disabled(),
                    ]),

                Section::make('Customer & Shipping Details')
                    ->description('Contact and delivery address across Bangladesh')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Recipient Name')
                            ->required(),
                        TextInput::make('customer_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('Email Address')
                            ->email(),
                        TextInput::make('shipping_division')
                            ->label('Division')
                            ->required(),
                        TextInput::make('shipping_district')
                            ->label('District')
                            ->required(),
                        TextInput::make('shipping_thana')
                            ->label('Thana / Area')
                            ->required(),
                        Textarea::make('shipping_address')
                            ->label('Street Address')
                            ->rows(2)
                            ->columnSpanFull()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Customer Delivery Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Purchased Footwear Items')
                    ->description('Snapshotted items at checkout')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->columns(['default' => 1, 'md' => 5])
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Product Name')
                                    ->disabled()
                                    ->columnSpan(['default' => 1, 'md' => 2]),
                                TextInput::make('unit_price')
                                    ->label('Unit Price (৳)')
                                    ->prefix('৳')
                                    ->numeric()
                                    ->disabled()
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->disabled()
                                    ->columnSpan(1),
                                TextInput::make('total_price')
                                    ->label('Item Total (৳)')
                                    ->prefix('৳')
                                    ->numeric()
                                    ->disabled()
                                    ->columnSpan(1),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Section::make('Financials Breakdown (BDT)')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('৳')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('delivery_charge')
                            ->label('Delivery Fee')
                            ->prefix('৳')
                            ->numeric()
                            ->required(),
                        TextInput::make('discount_amount')
                            ->label('Discount')
                            ->prefix('৳')
                            ->numeric()
                            ->default(0.00),
                        TextInput::make('total')
                            ->label('Grand Total')
                            ->prefix('৳')
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}
