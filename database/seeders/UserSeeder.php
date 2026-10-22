<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Filament Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@brand.com'],
            [
                'name' => 'Store Admin',
                'phone' => '01711000001',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Staff Member
        User::updateOrCreate(
            ['email' => 'staff@brand.com'],
            [
                'name' => 'Support Staff',
                'phone' => '01711000002',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Customer Users with Addresses
        $customers = [
            [
                'name' => 'Nusrat Jahan',
                'email' => 'nusrat@gmail.com',
                'phone' => '01712345678',
                'address' => [
                    'division' => 'Dhaka',
                    'district' => 'Dhaka',
                    'thana' => 'Dhanmondi',
                    'address_line' => 'House 14, Road 8/A, Flat 4B',
                    'postal_code' => '1209',
                ],
            ],
            [
                'name' => 'Ayesha Siddiqua',
                'email' => 'ayesha@gmail.com',
                'phone' => '01812345679',
                'address' => [
                    'division' => 'Dhaka',
                    'district' => 'Dhaka',
                    'thana' => 'Gulshan-2',
                    'address_line' => 'Apartment 7A, Road 54',
                    'postal_code' => '1212',
                ],
            ],
            [
                'name' => 'Sadia Islam',
                'email' => 'sadia@gmail.com',
                'phone' => '01912345680',
                'address' => [
                    'division' => 'Dhaka',
                    'district' => 'Dhaka',
                    'thana' => 'Uttara Sector 11',
                    'address_line' => 'House 22, Road 4',
                    'postal_code' => '1230',
                ],
            ],
            [
                'name' => 'Farhana Rahman',
                'email' => 'farhana@gmail.com',
                'phone' => '01612345681',
                'address' => [
                    'division' => 'Chittagong',
                    'district' => 'Chittagong',
                    'thana' => 'Panchlaish',
                    'address_line' => 'Nasirabad Housing Society, Road 3, House 7',
                    'postal_code' => '4203',
                ],
            ],
        ];

        foreach ($customers as $cust) {
            $user = User::updateOrCreate(
                ['email' => $cust['email']],
                [
                    'name' => $cust['name'],
                    'phone' => $cust['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            Address::updateOrCreate(
                ['user_id' => $user->id, 'phone' => $cust['phone']],
                [
                    'name' => $cust['name'],
                    'type' => 'shipping',
                    'phone' => $cust['phone'],
                    'division' => $cust['address']['division'],
                    'district' => $cust['address']['district'],
                    'thana' => $cust['address']['thana'],
                    'address_line' => $cust['address']['address_line'],
                    'postal_code' => $cust['address']['postal_code'],
                    'is_default' => true,
                ]
            );
        }
    }
}
