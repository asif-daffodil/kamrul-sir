<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            [
                'name' => 'Dhanmondi Flagship Store',
                'address' => 'House 42, Road 27 (Old), Dhanmondi R/A, Dhaka 1209',
                'district' => 'Dhaka',
                'phone' => '+8801711002233',
                'map_link' => 'https://maps.google.com/?q=23.7538,90.3762',
                'opening_hours' => '10:30 AM - 09:30 PM (Everyday)',
            ],
            [
                'name' => 'Banani 11 Experience Center',
                'address' => 'Plot 68, Block D, Road 11, Banani, Dhaka 1213',
                'district' => 'Dhaka',
                'phone' => '+8801711002234',
                'map_link' => 'https://maps.google.com/?q=23.7937,90.4043',
                'opening_hours' => '11:00 AM - 10:00 PM (Everyday)',
            ],
            [
                'name' => 'Uttara Sector 7 Boutique',
                'address' => 'Shop 204, RAK Tower, Jashimuddin Avenue, Sector 3/7, Uttara, Dhaka 1230',
                'district' => 'Dhaka',
                'phone' => '+8801711002235',
                'map_link' => 'https://maps.google.com/?q=23.8683,90.3986',
                'opening_hours' => '10:30 AM - 09:30 PM (Closed Tuesday)',
            ],
            [
                'name' => 'Bashundhara City Showroom',
                'address' => 'Shop 45, Level 3, Block B, Bashundhara City Shopping Complex, Panthapath, Dhaka 1215',
                'district' => 'Dhaka',
                'phone' => '+8801711002236',
                'map_link' => 'https://maps.google.com/?q=23.7509,90.3905',
                'opening_hours' => '10:00 AM - 09:00 PM (Closed Tuesday)',
            ],
            [
                'name' => 'Jamuna Future Park Store',
                'address' => 'Shop 1A-024, Ground Floor, Jamuna Future Park, Kuril, Dhaka 1229',
                'district' => 'Dhaka',
                'phone' => '+8801711002237',
                'map_link' => 'https://maps.google.com/?q=23.8135,90.4242',
                'opening_hours' => '11:00 AM - 09:30 PM (Closed Wednesday)',
            ],
            [
                'name' => 'Chattogram GEC Circle Outlet',
                'address' => 'Afmi Plaza, Level 2, Shop 218, Nasirabad, GEC Circle, Chattogram 4000',
                'district' => 'Chittagong',
                'phone' => '+8801711002238',
                'map_link' => 'https://maps.google.com/?q=22.3592,91.8219',
                'opening_hours' => '10:30 AM - 09:30 PM (Everyday)',
            ],
        ];

        foreach ($outlets as $outlet) {
            Outlet::updateOrCreate(
                ['slug' => Str::slug($outlet['name'])],
                [
                    'name' => $outlet['name'],
                    'address' => $outlet['address'],
                    'district' => $outlet['district'],
                    'phone' => $outlet['phone'],
                    'map_link' => $outlet['map_link'],
                    'opening_hours' => $outlet['opening_hours'],
                    'is_active' => true,
                ]
            );
        }
    }
}
