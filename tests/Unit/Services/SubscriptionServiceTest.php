<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;
use App\Core\Application\Services\SubscriptionService;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $subscriptionService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionService = app(SubscriptionService::class);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_trial_subscription()
    {
        $subscription = $this->subscriptionService->createTrial($this->user);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals(SubscriptionType::TRIAL, $subscription->type);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
        $this->assertNotNull($subscription->grace_ends_at);
    }

    /** @test */
    public function it_prevents_duplicate_trial_subscriptions()
    {
        // Create existing trial subscription
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User already has an active subscription');

        $this->subscriptionService->createTrial($this->user);
    }

    /** @test */
    public function it_can_check_active_subscription()
    {
        // Create active subscription
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
            'ends_at' => now()->addDays(30),
        ]);

        $isActive = $this->subscriptionService->checkActive($this->user);
        $this->assertTrue($isActive);
    }

    /** @test */
    public function it_returns_false_for_expired_subscription()
    {
        // Create expired subscription
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
            'ends_at' => now()->subDays(1),
        ]);

        $isActive = $this->subscriptionService->checkActive($this->user);
        $this->assertFalse($isActive);
    }

    /** @test */
    public function it_returns_true_for_grace_period_subscription()
    {
        // Create subscription in grace period
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
            'ends_at' => now()->subDays(1),
            'grace_ends_at' => now()->addDays(6),
        ]);

        $isActive = $this->subscriptionService->checkActive($this->user);
        $this->assertTrue($isActive);
    }

    /** @test */
    public function it_can_cancel_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $result = $this->subscriptionService->cancel($this->user);

        $this->assertTrue($result);
        $this->assertEquals(SubscriptionStatus::CANCELED, $subscription->fresh()->status);
    }

    /** @test */
    public function it_throws_exception_when_canceling_nonexistent_subscription()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No active subscription found');

        $this->subscriptionService->cancel($this->user);
    }

    /** @test */
    public function it_can_get_current_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $current = $this->subscriptionService->getCurrent($this->user);

        $this->assertInstanceOf(Subscription::class, $current);
        $this->assertEquals($subscription->id, $current->id);
    }

    /** @test */
    public function it_returns_null_when_no_subscription_exists()
    {
        $current = $this->subscriptionService->getCurrent($this->user);
        $this->assertNull($current);
    }

    /** @test */
    public function it_can_expire_overdue_subscriptions()
    {
        // Create overdue subscriptions
        $subscription1 = Subscription::factory()->create([
            'status' => SubscriptionStatus::ACTIVE,
            'grace_ends_at' => now()->subDays(1),
        ]);

        $subscription2 = Subscription::factory()->create([
            'status' => SubscriptionStatus::ACTIVE,
            'grace_ends_at' => now()->subDays(2),
        ]);

        $expiredCount = $this->subscriptionService->expireOverdue();

        $this->assertEquals(2, $expiredCount);
        $this->assertEquals(SubscriptionStatus::EXPIRED, $subscription1->fresh()->status);
        $this->assertEquals(SubscriptionStatus::EXPIRED, $subscription2->fresh()->status);
    }

    /** @test */
    public function it_does_not_expire_active_subscriptions()
    {
        // Create active subscription not in grace period
        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::ACTIVE,
            'grace_ends_at' => now()->addDays(7),
        ]);

        $expiredCount = $this->subscriptionService->expireOverdue();

        $this->assertEquals(0, $expiredCount);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->fresh()->status);
    }
}
