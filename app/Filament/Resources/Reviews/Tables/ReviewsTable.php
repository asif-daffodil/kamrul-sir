<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('Guest Customer'),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->color('amber')
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(50)
                    ->tooltip(fn (Review $record): ?string => $record->comment),
                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval Status')
                    ->trueLabel('Approved Reviews Only')
                    ->falseLabel('Pending Moderation Only'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Review $record): bool => ! $record->is_approved)
                    ->action(function (Review $record) {
                        $record->approve();
                        Notification::make()
                            ->title('Review approved and published to product page!')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->is_approved)
                    ->action(function (Review $record) {
                        $record->update(['is_approved' => false, 'approved_at' => null]);
                        Notification::make()
                            ->title('Review unapproved and hidden.')
                            ->warning()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
