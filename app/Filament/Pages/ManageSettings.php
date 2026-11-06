<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Store Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'promo_bar_enabled' => Setting::get('promo_bar_enabled', true),
            'promo_bar_text' => Setting::get('promo_bar_text', 'Complimentary Delivery on orders over ৳3,000 • Cash on Delivery in all 64 districts • Extra 5% OFF on bKash / Card Prepaid'),
            'prepaid_discount_percent' => Setting::get('prepaid_discount_percent', 5),
            'inside_dhaka_delivery_fee' => Setting::get('inside_dhaka_delivery_fee', '70.00'),
            'outside_dhaka_delivery_fee' => Setting::get('outside_dhaka_delivery_fee', '130.00'),
            'sub_dhaka_delivery_fee' => Setting::get('sub_dhaka_delivery_fee', '100.00'),
            'free_delivery_threshold' => Setting::get('free_delivery_threshold', '3000.00'),
            'support_phone' => Setting::get('support_phone', '+8801700000000'),
            'support_email' => Setting::get('support_email', 'care@brandfootwear.com'),
            'store_address' => Setting::get('store_address', 'Dhanmondi, Dhaka, Bangladesh'),
            'social_facebook' => Setting::get('social_facebook', 'https://facebook.com/brandfootwear'),
            'social_instagram' => Setting::get('social_instagram', 'https://instagram.com/brandfootwear'),
            'social_whatsapp' => Setting::get('social_whatsapp', '+8801700000000'),
            'bkash_merchant_number' => Setting::get('bkash_merchant_number', '01700000000'),
            'cod_available' => Setting::get('cod_available', true),
            'exchange_window_days' => Setting::get('exchange_window_days', 7),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Top Promo Bar (Storefront Header)')
                    ->description('Manage promotional announcement bar displayed across all storefront pages')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        Toggle::make('promo_bar_enabled')
                            ->label('Enable Top Promo Bar')
                            ->default(true),
                        TextInput::make('prepaid_discount_percent')
                            ->label('Extra Prepaid Discount (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(5)
                            ->helperText('Promotional extra discount on bKash or card prepaid orders'),
                        TextInput::make('promo_bar_text')
                            ->label('Custom Announcement Text')
                            ->placeholder('Complimentary Delivery on orders over ৳3,000...')
                            ->columnSpanFull()
                            ->helperText('Leave empty to auto-compose from delivery threshold & prepaid discount'),
                    ]),

                Section::make('Delivery Fees & Free Shipping Thresholds')
                    ->description('Set regional courier charges across Bangladesh')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('inside_dhaka_delivery_fee')
                            ->label('Inside Dhaka Delivery (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                        TextInput::make('outside_dhaka_delivery_fee')
                            ->label('Outside Dhaka Delivery (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                        TextInput::make('sub_dhaka_delivery_fee')
                            ->label('Dhaka Suburbs (Savar, Gazipur, Narayanganj) (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                        TextInput::make('free_delivery_threshold')
                            ->label('Free Shipping Eligible Cart Minimum (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->helperText('Orders exceeding this subtotal automatically receive zero delivery charge')
                            ->required(),
                    ]),

                Section::make('Store Customer Support & Contact Information')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('support_phone')
                            ->label('Customer Helpline')
                            ->tel()
                            ->required(),
                        TextInput::make('support_email')
                            ->label('Support Email')
                            ->email()
                            ->required(),
                        TextInput::make('exchange_window_days')
                            ->label('Exchange Allowed Window (Days)')
                            ->numeric()
                            ->required(),
                        Textarea::make('store_address')
                            ->label('Head Office / Central Hub Address')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Social Channels & Messenger Links')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('social_facebook')
                            ->label('Facebook Page URL')
                            ->url(),
                        TextInput::make('social_instagram')
                            ->label('Instagram Profile URL')
                            ->url(),
                        TextInput::make('social_whatsapp')
                            ->label('WhatsApp Support Number')
                            ->placeholder('+88017xxxxxxxx'),
                    ]),

                Section::make('Payment Policies & Gateways')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('bkash_merchant_number')
                            ->label('bKash Merchant Account Number'),
                        Toggle::make('cod_available')
                            ->label('Cash on Delivery (COD) Enabled')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $group = match (true) {
                str_contains($key, 'promo_') => 'announcement',
                str_contains($key, 'fee') || str_contains($key, 'threshold') => 'delivery',
                str_contains($key, 'phone') || str_contains($key, 'email') || str_contains($key, 'address') => 'contact',
                str_contains($key, 'social_') => 'social',
                str_contains($key, 'bkash') || str_contains($key, 'cod') || str_contains($key, 'prepaid') => 'payment',
                default => 'general',
            };

            Setting::set($key, $value, $group);
        }

        Notification::make()
            ->title('Store settings saved successfully!')
            ->success()
            ->send();
    }
}
