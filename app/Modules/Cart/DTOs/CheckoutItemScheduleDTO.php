<?php

namespace App\Modules\Cart\DTOs;

use Carbon\Carbon;

class CheckoutItemScheduleDTO
{
    public function __construct(
        public readonly int $cart_item_id,
        public readonly int $service_id,
        public readonly Carbon $scheduled_at
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cart_item_id: $data['cart_item_id'],
            service_id: $data['service_id'],
            scheduled_at: Carbon::parse($data['scheduled_at'])
        );
    }

    public function toArray(): array
    {
        return [
            'cart_item_id' => $this->cart_item_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at->toISOString(),
        ];
    }
}
