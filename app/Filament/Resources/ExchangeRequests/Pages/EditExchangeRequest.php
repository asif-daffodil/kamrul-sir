<?php

namespace App\Filament\Resources\ExchangeRequests\Pages;

use App\Filament\Resources\ExchangeRequests\ExchangeRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExchangeRequest extends EditRecord
{
    protected static string $resource = ExchangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
