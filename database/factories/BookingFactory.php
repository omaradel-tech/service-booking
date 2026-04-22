<?php

namespace Database\Factories;

use App\Core\Domain\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Booking\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'service_id' => Service::factory(),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => BookingStatus::PENDING(),
        ];
    }

    /**
     * Create a pending booking.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::PENDING(),
        ]);
    }

    /**
     * Create a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CONFIRMED(),
        ]);
    }

    /**
     * Create a completed booking.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::COMPLETED(),
            'scheduled_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create a canceled booking.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CANCELED(),
        ]);
    }

    /**
     * Create a booking for a specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Create a booking for a specific service.
     */
    public function forService(int $serviceId): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $serviceId,
        ]);
    }

    /**
     * Create a booking scheduled for a specific time.
     */
    public function scheduledAt(\DateTime $dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => $dateTime,
        ]);
    }
}
