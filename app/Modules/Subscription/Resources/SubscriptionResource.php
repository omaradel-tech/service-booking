<?php

namespace App\Modules\Subscription\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'grace_ends_at' => $this->grace_ends_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_active' => $this->isActive(),
            'is_in_grace_period' => $this->isInGracePeriod(),
            'days_remaining' => $this->when($this->isActive(), function () {
                return $this->ends_at->diffInDays(now(), false);
            }),
        ];
    }
}
