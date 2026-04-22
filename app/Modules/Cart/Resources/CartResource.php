<?php

namespace App\Modules\Cart\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user_id' => $this->user_id,
            'total_price' => $this->total_price,
            'items_count' => $this->items->sum('quantity'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
