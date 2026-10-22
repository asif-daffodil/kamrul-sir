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
            $comment = $this->data['status_comment'] ?? 'Status updated by admin/operations team.';

            // Restock variants if cancelling or returning an active order
            if (in_array($newStatus, ['cancelled', 'returned']) && ! in_array($this->previousStatus, ['cancelled', 'returned'])) {
                $this->record->loadMissing('items.variant');
                foreach ($this->record->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock', $item->quantity);
                    }
                }
            }

            OrderStatusHistory::create([
                'order_id' => $this->record->id,
                'from_status' => $this->previousStatus,
                'to_status' => $newStatus,
                'changed_by' => auth()->id(),
                'comment' => $comment,
            ]);

            $this->record->notify(new \App\Notifications\OrderStatusUpdatedNotification(
                $this->record,
                $this->previousStatus,
                $newStatus,
                $comment
            ));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_to_courier')
                ->label('Send to Courier')
                ->icon(Heroicon::OutlinedTruck)
                ->color('primary')
                ->modalHeading(fn () => 'Book Courier Consignment for #' . $this->record->order_number)
                ->modalDescription('Create parcel consignment with Bangladeshi courier and generate tracking number.')
                ->visible(fn () => empty($this->record->courier_tracking_code) && ! in_array($this->record->status, ['cancelled', 'delivered']))
                ->form([
                    \Filament\Forms\Components\Select::make('courier')
                        ->label('Select Courier Service')
                        ->options([
                            'steadfast' => 'Steadfast Courier (BD Nationwide)',
                            'pathao' => 'Pathao Courier (Express / Standard)',
                        ])
                        ->default(config('courier.default', 'steadfast'))
                        ->required()
                        ->native(false),
                    \Filament\Forms\Components\TextInput::make('note')
                        ->label('Delivery Instructions / Note')
                        ->default(fn () => $this->record->notes ?: 'Women Footwear - Handle with care')
                        ->helperText('Courier rider instructions (e.g. Call before delivery)'),
                ])
                ->action(function (array $data) {
                    /** @var \App\Services\Courier\CourierManager $manager */
                    $manager = app(\App\Services\Courier\CourierManager::class);

                    $result = $manager->sendOrderToCourier(
                        order: $this->record,
                        driverName: $data['courier'],
                        options: ['note' => $data['note'] ?? null]
                    );

                    if ($result->successful) {
                        $this->record->refresh();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Consignment created with ' . ucfirst($data['courier']))
                            ->body("Tracking Code: {$this->record->courier_tracking_code}")
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Courier Booking Failed')
                            ->body($result->message)
                            ->persistent()
                            ->send();
                    }
                }),
            Action::make('sync_courier_status')
                ->label('Sync Status')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('slate')
                ->visible(fn () => ! empty($this->record->courier_tracking_code))
                ->action(function () {
                    \App\Jobs\SyncCourierDeliveryStatusJob::dispatchSync($this->record->id);

                    $this->record->refresh();
                    \Filament\Notifications\Notification::make()
                        ->info()
                        ->title('Status Synced')
                        ->body("Courier Status: " . ucfirst($this->record->courier_status ?? 'Unknown'))
                        ->send();
                }),
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
