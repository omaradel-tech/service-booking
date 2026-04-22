<?php

namespace App\Modules\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total price of all items in the cart.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            // Avoid accessing the polymorphic relationship directly
            // Instead, calculate based on item type and ID
            try {
                if ($item->item_type === 'service') {
                    $service = \App\Modules\Service\Models\Service::find($item->item_id);
                    return $service ? $item->quantity * $service->price : 0;
                } elseif ($item->item_type === 'package') {
                    $package = \App\Modules\Package\Models\Package::find($item->item_id);
                    return $package ? $item->quantity * $package->price : 0;
                }
                return 0;
            } catch (\Exception $e) {
                // If there's any error accessing the item, return 0 for this item
                return 0;
            }
        });
    }

    /**
     * Add an item to the cart.
     */
    public function addItem($itemType, $itemId, $quantity = 1)
    {
        $existingItem = $this->items()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        }

        return $this->items()->create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem($itemType, $itemId)
    {
        return $this->items()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete();
    }

    /**
     * Clear all items from the cart.
     */
    public function clear()
    {
        return $this->items()->delete();
    }
}
