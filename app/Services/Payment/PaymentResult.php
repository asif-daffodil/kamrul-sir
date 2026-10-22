<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public bool $successful,
        public ?string $transactionId = null,
        public string $status = 'pending', // 'paid', 'pending', 'failed', 'refunded'
        public ?string $redirectUrl = null,
        public ?string $message = null,
        public array $details = []
    ) {}
}
