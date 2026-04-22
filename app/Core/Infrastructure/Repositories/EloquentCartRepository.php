<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Contracts\CartRepositoryInterface;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;

class EloquentCartRepository implements CartRepositoryInterface
{
    /**
     * Get or create cart for user.
     */
    public function getOrCreateForUser(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Add item to cart.
     */
    public function addItem(Cart $cart, string $itemType, int $itemId, int $quantity = 1): CartItem
    {
        $existingItem = $cart->items()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem->fresh();
        }

        return $cart->items()->create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Cart $cart, string $itemType, int $itemId): bool
    {
        return $cart->items()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete() > 0;
    }

    /**
     * Update item quantity.
     */
    public function updateQuantity(CartItem $item, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $item->delete();
        }

        return $item->update(['quantity' => $quantity]);
    }

    /**
     * Clear cart.
     */
    public function clearCart(Cart $cart): bool
    {
        return $cart->items()->delete() > 0;
    }

    /**
     * Find cart item by ID.
     */
    public function findItem(int $id): ?CartItem
    {
        return CartItem::with('cart')->find($id);
    }

    /**
     * Get cart by user.
     */
    public function getByUser(User $user): ?Cart
    {
        return Cart::with('items')->where('user_id', $user->id)->first();
    }
}
