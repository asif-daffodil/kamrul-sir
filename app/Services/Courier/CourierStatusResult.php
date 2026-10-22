<?php

namespace App\Services\Courier;

class CourierStatusResult
{
    public function __construct(
        public bool $successful,
        public ?string $status = null, // Normalized: pending, in_transit, delivered, cancelled, returned, unknown
        public ?string $rawStatus = null,
        public ?string $message = null,
        public array $rawResponse = []
    ) {}
}
