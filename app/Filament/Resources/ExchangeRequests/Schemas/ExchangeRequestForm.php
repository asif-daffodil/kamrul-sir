<?php

namespace App\Filament\Resources\ExchangeRequests\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExchangeRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Exchange Request Details')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('reason')
                            ->label('Exchange Reason')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved (Awaiting Return)',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed / Replacement Sent',
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        FileUpload::make('photo_path')
                            ->label('Uploaded Footwear Inspection Photo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('exchange-photos')
                            ->openable()
                            ->downloadable(),
                        Textarea::make('customer_notes')
                            ->label('Customer Remarks')
                            ->rows(3),
                        Textarea::make('admin_notes')
                            ->label('Internal Resolution Notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
