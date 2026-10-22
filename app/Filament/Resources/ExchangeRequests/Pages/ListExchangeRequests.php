<?php

namespace App\Filament\Resources\ExchangeRequests\Pages;

use App\Filament\Resources\ExchangeRequests\ExchangeRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExchangeRequests extends ListRecords
{
    protected static string $resource = ExchangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
