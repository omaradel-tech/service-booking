<?php

namespace Database\Factories;

use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use App\Modules\Subscription\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Subscription\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => SubscriptionType::TRIAL(),
            'status' => SubscriptionStatus::ACTIVE(),
            'starts_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'grace_ends_at' => $this->faker->dateTimeBetween('+1 year', '+1 year + 7 days'),
        ];
    }

    /**
     * Create a trial subscription.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SubscriptionType::TRIAL(),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'grace_ends_at' => now()->addDays(37),
        ]);
    }

    /**
     * Create a paid subscription.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SubscriptionType::PAID(),
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'grace_ends_at' => now()->addYear()->addDays(7),
        ]);
    }

    /**
     * Create an expired subscription.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::EXPIRED(),
            'ends_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'grace_ends_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Create a canceled subscription.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::CANCELED(),
        ]);
    }

    /**
     * Create a subscription for a specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
