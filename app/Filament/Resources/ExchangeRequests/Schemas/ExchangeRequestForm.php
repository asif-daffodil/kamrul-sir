<?php

namespace App\Filament\Resources\ExchangeRequests\Schemas;

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
            ->components([
                Section::make('Exchange Request Details')
                    ->columns(2)
                    ->schema([
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
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
                            ->required(),
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
