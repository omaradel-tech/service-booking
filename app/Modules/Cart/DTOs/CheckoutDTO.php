<?php

namespace App\Modules\Cart\DTOs;

class CheckoutDTO
{
    public function __construct(
        public readonly array $schedules // array of CheckoutItemScheduleDTO
    ) {}

    public static function fromArray(array $data): self
    {
        $schedules = array_map(
            fn($schedule) => CheckoutItemScheduleDTO::fromArray($schedule),
            $data['schedules'] ?? []
        );

        return new self(schedules: $schedules);
    }

    public function toArray(): array
    {
        return [
            'schedules' => array_map(
                fn($schedule) => $schedule->toArray(),
                $this->schedules
            )
        ];
    }
}
