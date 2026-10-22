<?php

namespace App\Support;

class BangladeshLocations
{
    /**
     * Map of 8 Divisions and their respective 64 Districts.
     */
    public const DIVISIONS = [
        'Dhaka' => [
            'Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Kishoreganj',
            'Manikganj', 'Munshiganj', 'Narsingdi', 'Faridpur', 'Gopalganj',
            'Madaripur', 'Rajbari', 'Shariatpur',
        ],
        'Chattogram' => [
            'Chattogram', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Brahmanbaria',
            'Noakhali', 'Chandpur', 'Lakshmipur', 'Rangamati', 'Bandarban', 'Khagrachhari',
        ],
        'Rajshahi' => [
            'Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon',
            'Natore', 'Chapainawabganj', 'Joypurhat',
        ],
        'Khulna' => [
            'Khulna', 'Jashore', 'Kushtia', 'Satkhira', 'Bagerhat',
            'Jhenaidah', 'Chuadanga', 'Magura', 'Meherpur', 'Narail',
        ],
        'Barishal' => [
            'Barishal', 'Patuakhali', 'Bhola', 'Pirojpur', 'Barguna', 'Jhalokati',
        ],
        'Sylhet' => [
            'Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj',
        ],
        'Rangpur' => [
            'Rangpur', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Nilphamari',
            'Lalmonirhat', 'Thakurgaon', 'Panchagarh',
        ],
        'Mymensingh' => [
            'Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur',
        ],
    ];

    /**
     * Major thanas/areas inside Dhaka Metropolitan area.
     */
    public const DHAKA_THANAS = [
        'Dhanmondi', 'Gulshan-1', 'Gulshan-2', 'Banani', 'Uttara',
        'Mirpur-1', 'Mirpur-2', 'Mirpur-10', 'Mirpur-11', 'Mirpur-12', 'Mirpur-14',
        'Mohammadpur', 'Badda', 'Motijheel', 'Old Dhaka (Puran Dhaka)',
        'Bashundhara R/A', 'Khilgaon', 'Rampura', 'Tejgaon', 'Malibagh',
        'Lalbagh', 'Shantinagar', 'Paltan', 'Baridhara', 'Nikunja',
        'Cantonment', 'Khilkhet', 'Savar', 'Keraniganj', 'Jatrabari',
    ];

    /**
     * Get list of division names.
     */
    public static function getDivisions(): array
    {
        return array_keys(self::DIVISIONS);
    }

    /**
     * Get districts for a division.
     */
    public static function getDistricts(string $division): array
    {
        return self::DIVISIONS[$division] ?? [];
    }

    /**
     * Standardize and validate a Bangladeshi mobile phone number.
     * Valid numbers: 013, 014, 015, 016, 017, 018, 019 followed by 8 digits (11 digits total).
     */
    public static function isValidPhone(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Strip leading 88 if present (e.g. 8801700000000 -> 01700000000)
        if (str_starts_with($cleaned, '88') && strlen($cleaned) === 13) {
            $cleaned = substr($cleaned, 2);
        }

        return (bool) preg_match('/^01[3-9]\d{8}$/', $cleaned);
    }

    /**
     * Normalize phone to standard 11-digit local format: 01XXXXXXXXX.
     */
    public static function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '88') && strlen($cleaned) === 13) {
            $cleaned = substr($cleaned, 2);
        }

        return $cleaned;
    }
}
