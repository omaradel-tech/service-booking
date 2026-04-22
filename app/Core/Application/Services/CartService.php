<?php

namespace App\Core\Application\Services;

use App\Core\Application\Contracts\CartRepositoryInterface;
use App\Core\Application\DTOs\AddCartItemDTO;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    /**
     * Add item to cart.
     */
    public function addItem(AddCartItemDTO $dto): CartItem
    {
        $cart = $this->cartRepository->getOrCreateForUser($dto->user);
        
        $cartItem = $this->cartRepository->addItem(
            $cart,
            $dto->itemType,
            $dto->itemId,
            $dto->quantity
        );

        Log::info('Item added to cart', [
            'user_id' => $dto->user->id,
            'item_type' => $dto->itemType,
            'item_id' => $dto->itemId,
            'quantity' => $dto->quantity,
        ]);

        return $cartItem;
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(User $user, string $itemType, int $itemId): bool
    {
        $cart = $this->cartRepository->getOrCreateForUser($user);
        
        $removed = $this->cartRepository->removeItem($cart, $itemType, $itemId);

        if ($removed) {
            Log::info('Item removed from cart', [
                'user_id' => $user->id,
                'item_type' => $itemType,
                'item_id' => $itemId,
            ]);
        }

        return $removed;
    }

    /**
     * Update item quantity.
     */
    public function updateQuantity(CartItem $item, int $quantity): bool
    {
        if ($quantity < 1) {
            throw new \Exception('Quantity must be at least 1');
        }

        $updated = $this->cartRepository->updateQuantity($item, $quantity);

        if ($updated) {
            Log::info('Cart item quantity updated', [
                'cart_item_id' => $item->id,
                'new_quantity' => $quantity,
            ]);
        }

        return $updated;
    }

    /**
     * Get user's cart.
     */
    public function getCart(User $user): Cart
    {
        return $this->cartRepository->getOrCreateForUser($user);
    }

    /**
     * Clear cart.
     */
    public function clearCart(User $user): bool
    {
        $cart = $this->cartRepository->getOrCreateForUser($user);
        
        $cleared = $this->cartRepository->clearCart($cart);

        if ($cleared) {
            Log::info('Cart cleared', [
                'user_id' => $user->id,
            ]);
        }

        return $cleared;
    }

    /**
     * Get cart total price.
     */
    public function getTotalPrice(User $user): float
    {
        $cart = $this->getCart($user);
        return $cart->total_price;
    }

    /**
     * Get cart items count.
     */
    public function getItemsCount(User $user): int
    {
        $cart = $this->getCart($user);
        return $cart->items->sum('quantity');
    }
}
