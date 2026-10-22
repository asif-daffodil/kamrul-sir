<?php

namespace App\Filament\Resources\ExchangeRequests;

use App\Filament\Resources\ExchangeRequests\Pages\CreateExchangeRequest;
use App\Filament\Resources\ExchangeRequests\Pages\EditExchangeRequest;
use App\Filament\Resources\ExchangeRequests\Pages\ListExchangeRequests;
use App\Filament\Resources\ExchangeRequests\Schemas\ExchangeRequestForm;
use App\Filament\Resources\ExchangeRequests\Tables\ExchangeRequestsTable;
use App\Models\ExchangeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class ExchangeRequestResource extends Resource
{
    protected static ?string $model = ExchangeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Sales & Orders';

    protected static ?string $navigationLabel = 'Exchange Requests';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['order', 'orderItem', 'user', 'requestedVariant']);
    }

    public static function form(Schema $schema): Schema
    {
        return ExchangeRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExchangeRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRequests::route('/'),
            'create' => CreateExchangeRequest::route('/create'),
            'edit' => EditExchangeRequest::route('/{record}/edit'),
        ];
    }
}
