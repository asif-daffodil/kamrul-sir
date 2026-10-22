<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Voucher Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Promo Code')
                            ->required()
                            ->unique(Coupon::class, 'code', ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                            ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                        Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'fixed' => 'Flat Amount (BDT)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->default('fixed')
                            ->required(),
                        TextInput::make('value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required(),
                        TextInput::make('min_spend')
                            ->label('Minimum Cart Subtotal (৳)')
                            ->prefix('৳')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('max_discount')
                            ->label('Maximum Discount Cap (৳)')
                            ->prefix('৳')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('usage_limit')
                            ->label('Total Usage Limit')
                            ->numeric()
                            ->placeholder('Unlimited if empty'),
                        DateTimePicker::make('starts_at')
                            ->label('Start Date & Time'),
                        DateTimePicker::make('expires_at')
                            ->label('Expiry Date & Time'),
                        Toggle::make('is_active')
                            ->label('Voucher Active')
                            ->default(true),
                    ]),
            ]);
    }
}
