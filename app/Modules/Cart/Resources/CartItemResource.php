<?php

namespace App\Modules\Cart\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'item_type' => $this->item_type,
            'item_id' => $this->item_id,
            'quantity' => $this->quantity,
            'total_price' => $this->when($this->relationLoaded('item'), function () {
                return $this->quantity * $this->item->price;
            }, 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'item' => $this->when($this->relationLoaded('item'), function () {
                if ($this->item_type === 'service') {
                    return [
                        'id' => $this->item->id,
                        'name' => $this->item->name,
                        'price' => $this->item->price,
                        'description' => $this->item->description,
                        'duration_minutes' => $this->item->duration_minutes,
                    ];
                } elseif ($this->item_type === 'package') {
                    return [
                        'id' => $this->item->id,
                        'name' => $this->item->name,
                        'price' => $this->item->price,
                        'discount_percentage' => $this->item->discount_percentage,
                    ];
                }
                return null;
            }),
        ];
    }
}
