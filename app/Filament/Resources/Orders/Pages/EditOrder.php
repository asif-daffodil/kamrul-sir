<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderStatusHistory;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $previousStatus = null;

    protected function beforeFill(): void
    {
        $this->previousStatus = $this->record->status;
    }

    protected function afterSave(): void
    {
        $newStatus = $this->record->status;

        if ($this->previousStatus !== null && $this->previousStatus !== $newStatus) {
            OrderStatusHistory::create([
                'order_id' => $this->record->id,
                'from_status' => $this->previousStatus,
                'to_status' => $newStatus,
                'changed_by' => auth()->id(),
                'comment' => $this->data['status_comment'] ?? 'Status updated by admin/operations team.',
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_invoice')
                ->label('Print Invoice')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('slate')
                ->url(fn () => route('admin.orders.invoice', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
