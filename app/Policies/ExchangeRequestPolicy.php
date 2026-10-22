<?php

namespace App\Policies;

use App\Models\ExchangeRequest;
use App\Models\User;

class ExchangeRequestPolicy
{
    /**
     * Determine whether the user can view any exchange requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isCustomer();
    }

    /**
     * Determine whether the user can view the exchange request.
     */
    public function view(User $user, ExchangeRequest $exchangeRequest): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $exchangeRequest->user_id !== null && (int) $exchangeRequest->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can create exchange requests.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the exchange request.
     */
    public function update(User $user, ExchangeRequest $exchangeRequest): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Determine whether the user can delete the exchange request.
     */
    public function delete(User $user, ExchangeRequest $exchangeRequest): bool
    {
        return $user->isAdmin();
    }
}
