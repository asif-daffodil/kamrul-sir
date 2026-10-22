<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Delivery Charges & Policies
            [
                'key' => 'inside_dhaka_delivery_fee',
                'value' => '70.00',
                'group' => 'delivery',
                'type' => 'decimal',
                'description' => 'Standard home delivery charge inside Dhaka City (BDT)',
            ],
            [
                'key' => 'sub_dhaka_delivery_fee',
                'value' => '100.00',
                'group' => 'delivery',
                'type' => 'decimal',
                'description' => 'Delivery charge for Dhaka suburbs (Savar, Gazipur, Keraniganj, Narayanganj)',
            ],
            [
                'key' => 'outside_dhaka_delivery_fee',
                'value' => '130.00',
                'group' => 'delivery',
                'type' => 'decimal',
                'description' => 'Standard courier delivery charge outside Dhaka across all 63 districts (BDT)',
            ],
            [
                'key' => 'free_delivery_threshold',
                'value' => '3000.00',
                'group' => 'delivery',
                'type' => 'decimal',
                'description' => 'Minimum cart subtotal eligible for complimentary free shipping (BDT)',
            ],
            [
                'key' => 'estimated_delivery_dhaka',
                'value' => '24 - 48 hours',
                'group' => 'delivery',
                'type' => 'string',
                'description' => 'Estimated turnaround time for delivery inside Dhaka',
            ],
            [
                'key' => 'estimated_delivery_outside',
                'value' => '3 - 5 business days',
                'group' => 'delivery',
                'type' => 'string',
                'description' => 'Estimated turnaround time for delivery outside Dhaka',
            ],

            // General & Store Branding
            [
                'key' => 'shop_name',
                'value' => '[BRAND_NAME]',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Official Brand Name for Store & Notifications',
            ],
            [
                'key' => 'shop_tagline',
                'value' => 'Handcrafted Footwear for Bangladeshi Women',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Marketing tagline displayed on headers and receipts',
            ],
            [
                'key' => 'support_phone',
                'value' => '+8801700000000',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Customer helpline phone number',
            ],
            [
                'key' => 'support_email',
                'value' => 'care@brandfootwear.com',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Customer support email address',
            ],

            // Policy & Exchanges
            [
                'key' => 'exchange_window_days',
                'value' => '7',
                'group' => 'policy',
                'type' => 'integer',
                'description' => 'Allowable timeframe for size and defect exchange requests after delivery (days)',
            ],
            [
                'key' => 'cod_available',
                'value' => 'true',
                'group' => 'payment',
                'type' => 'boolean',
                'description' => 'Enable or disable Cash on Delivery across Bangladesh',
            ],
            [
                'key' => 'bkash_merchant_number',
                'value' => '01700000000',
                'group' => 'payment',
                'type' => 'string',
                'description' => 'Official bKash merchant account phone number',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
