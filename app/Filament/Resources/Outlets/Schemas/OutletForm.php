<?php

namespace App\Filament\Resources\Outlets\Schemas;

use App\Models\Outlet;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OutletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Physical Storefront Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Showroom Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Outlet::class, 'slug', ignoreRecord: true),
                        Select::make('district')
                            ->options([
                                'Dhaka' => 'Dhaka',
                                'Chittagong' => 'Chittagong',
                                'Sylhet' => 'Sylhet',
                                'Rajshahi' => 'Rajshahi',
                                'Khulna' => 'Khulna',
                            ])
                            ->required(),
                        TextInput::make('phone')
                            ->label('Store Phone')
                            ->tel()
                            ->required(),
                        TextInput::make('opening_hours')
                            ->placeholder('e.g. 10:30 AM - 09:30 PM (Everyday)'),
                        TextInput::make('map_link')
                            ->label('Google Maps Location URL')
                            ->url()
                            ->placeholder('https://maps.google.com/?q=...'),
                        Textarea::make('address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Show on Website')
                            ->default(true),
                    ]),
            ]);
    }
}
