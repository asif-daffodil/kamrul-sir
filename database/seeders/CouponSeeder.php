<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_spend' => 1500.00,
                'max_discount' => 300.00,
                'usage_limit' => 1000,
                'used_count' => 14,
                'starts_at' => now()->subMonths(1),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'EID2026',
                'type' => 'percentage',
                'value' => 15.00,
                'min_spend' => 2500.00,
                'max_discount' => 600.00,
                'usage_limit' => 500,
                'used_count' => 88,
                'starts_at' => now()->subDays(10),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'fixed',
                'value' => 70.00, // Covers inside-Dhaka delivery fee
                'min_spend' => 1200.00,
                'max_discount' => null,
                'usage_limit' => 300,
                'used_count' => 45,
                'starts_at' => now()->subDays(5),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'HEELS200',
                'type' => 'fixed',
                'value' => 200.00,
                'min_spend' => 2000.00,
                'max_discount' => null,
                'usage_limit' => 200,
                'used_count' => 19,
                'starts_at' => now()->subDays(2),
                'expires_at' => now()->addMonths(1),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
