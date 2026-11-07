<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Customer / Account Profile')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel()
                            ->placeholder('017xxxxxxxx')
                            ->unique(User::class, 'phone', ignoreRecord: true),
                        Select::make('role')
                            ->options([
                                'customer' => 'Customer',
                                'staff' => 'Staff Member',
                                'admin' => 'Administrator',
                            ])
                            ->default('customer')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Account Active')
                            ->default(true),
                    ]),
            ]);
    }
}
