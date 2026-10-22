<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class MergeGuestCartOnLogin
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $sessionId = Session::getId();

        if ($sessionId && $event->user) {
            $this->cartService->mergeGuestCart($event->user, $sessionId);
        }
    }
}
