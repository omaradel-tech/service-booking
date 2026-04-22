<?php

namespace App\Core\Application\Contracts;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;

interface CartRepositoryInterface
{
    /**
     * Get or create cart for user.
     */
    public function getOrCreateForUser(User $user): Cart;

    /**
     * Add item to cart.
     */
    public function addItem(Cart $cart, string $itemType, int $itemId, int $quantity = 1): CartItem;

    /**
     * Remove item from cart.
     */
    public function removeItem(Cart $cart, string $itemType, int $itemId): bool;

    /**
     * Update item quantity.
     */
    public function updateQuantity(CartItem $item, int $quantity): bool;

    /**
     * Clear cart.
     */
    public function clearCart(Cart $cart): bool;

    /**
     * Find cart item by ID.
     */
    public function findItem(int $id): ?CartItem;

    /**
     * Get cart by user.
     */
    public function getByUser(User $user): ?Cart;
}
