<?php

namespace App\Services\Courier;

class CourierConsignmentResult
{
    public function __construct(
        public bool $successful,
        public ?string $consignmentId = null,
        public ?string $trackingCode = null,
        public ?string $status = null,
        public ?string $message = null,
        public array $rawResponse = []
    ) {}
}
