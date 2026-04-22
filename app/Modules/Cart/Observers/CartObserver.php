<?php

namespace App\Modules\Cart\Observers;

use App\Modules\Cart\Models\Cart;
use Illuminate\Support\Facades\Log;

class CartObserver
{
    /**
     * Handle the Cart "created" event.
     */
    public function created(Cart $cart): void
    {
        Log::info('Cart created', [
            'cart_id' => $cart->id,
            'user_id' => $cart->user_id,
        ]);
    }

    /**
     * Handle the Cart "updated" event.
     */
    public function updated(Cart $cart): void
    {
        if ($cart->wasChanged('total_price')) {
            Log::info('Cart total price updated', [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'old_total' => $cart->getOriginal('total_price'),
                'new_total' => $cart->total_price,
            ]);
        }
    }

    /**
     * Handle the Cart "deleted" event.
     */
    public function deleted(Cart $cart): void
    {
        Log::info('Cart deleted', [
            'cart_id' => $cart->id,
            'user_id' => $cart->user_id,
        ]);
    }
}
