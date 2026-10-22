<?php

namespace App\Rules;

use App\Support\BangladeshLocations;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BangladeshPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! BangladeshLocations::isValidPhone($value)) {
            $fail('Please enter a valid 11-digit Bangladeshi mobile number (e.g., 01712345678).');
        }
    }
}
