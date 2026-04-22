<?php

namespace App\Modules\Cart\Observers;

use App\Modules\Cart\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        // Update cart total price
        $cartItem->cart->updateTotalPrice();

        Log::info('Cart item created', [
            'cart_item_id' => $cartItem->id,
            'cart_id' => $cartItem->cart_id,
            'item_type' => $cartItem->item_type,
            'item_id' => $cartItem->item_id,
            'quantity' => $cartItem->quantity,
        ]);
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        // Update cart total price if quantity or item changed
        if ($cartItem->wasChanged(['quantity', 'item_id'])) {
            $cartItem->cart->updateTotalPrice();
        }

        Log::info('Cart item updated', [
            'cart_item_id' => $cartItem->id,
            'cart_id' => $cartItem->cart_id,
            'changes' => $cartItem->getChanges(),
        ]);
    }

    /**
     * Handle the CartItem "deleted" event.
     */
    public function deleted(CartItem $cartItem): void
    {
        // Update cart total price
        if ($cartItem->cart) {
            $cartItem->cart->updateTotalPrice();
        }

        Log::info('Cart item deleted', [
            'cart_item_id' => $cartItem->id,
            'cart_id' => $cartItem->cart_id,
            'item_type' => $cartItem->item_type,
            'item_id' => $cartItem->item_id,
        ]);
    }
}
