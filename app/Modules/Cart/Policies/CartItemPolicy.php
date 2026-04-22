<?php

namespace App\Modules\Cart\Policies;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;

class CartItemPolicy
{
    /**
     * Determine if the user can view the cart item.
     */
    public function view(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    /**
     * Determine if the user can update the cart item.
     */
    public function update(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    /**
     * Determine if the user can delete the cart item.
     */
    public function delete(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    /**
     * Determine if the user can create cart items.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create cart items
    }
}
