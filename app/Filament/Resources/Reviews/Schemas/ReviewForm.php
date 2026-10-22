<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Review Details')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->nullable(),
                        Select::make('rating')
                            ->options([
                                5 => '⭐⭐⭐⭐⭐ (5/5)',
                                4 => '⭐⭐⭐⭐ (4/5)',
                                3 => '⭐⭐⭐ (3/5)',
                                2 => '⭐⭐ (2/5)',
                                1 => '⭐ (1/5)',
                            ])
                            ->required(),
                        Toggle::make('is_approved')
                            ->label('Approved & Published')
                            ->helperText('Visible to store visitors on product page')
                            ->default(false),
                        Textarea::make('comment')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
