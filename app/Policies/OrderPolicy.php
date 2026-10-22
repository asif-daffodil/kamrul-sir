<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isCustomer();
    }

    /**
     * Determine whether the user can view the specific order.
     * Enforces strict authorization: Admin/Staff can view all orders,
     * customers can only view their own order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $order->user_id !== null && (int) $order->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return (int) $order->user_id === (int) $user->id && $order->status === 'pending';
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
