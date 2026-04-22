<?php

namespace App\Modules\Cart\Models;

use App\Core\Domain\Enums\CartItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'item_type',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the parent item (service or package).
     */
    public function item()
    {
        return $this->morphTo('item', [
            'service' => \App\Modules\Service\Models\Service::class,
            'package' => \App\Modules\Package\Models\Package::class,
        ]);
    }

    /**
     * Get the total price for this cart item.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->item->price;
    }

    /**
     * Update the quantity of the cart item.
     */
    public function updateQuantity($quantity)
    {
        if ($quantity <= 0) {
            return $this->delete();
        }

        $this->update(['quantity' => $quantity]);
        return $this;
    }
}
