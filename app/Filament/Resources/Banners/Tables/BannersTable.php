<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(40),
                ImageColumn::make('image_path')
                    ->label('Shoe Visual')
                    ->circular(false)
                    ->defaultImageUrl(fn (): string => asset('images/banners/hero-heels.webp')),
                TextColumn::make('title')
                    ->label('Slide Headline')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->subtitle),
                TextColumn::make('badge')
                    ->label('Badge')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('button_text')
                    ->label('CTA')
                    ->color('gray'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
