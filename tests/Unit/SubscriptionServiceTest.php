<?php

use App\Core\Application\Services\SubscriptionService;
use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use App\Models\User;
use App\Modules\Subscription\Models\Subscription;
use Mockery as m;

test('subscription service can create trial subscription', function () {
    $user = new class extends User {
        public int $id = 1;
        public function __construct() {}
        public function setAttribute($key, $value) { return null; }
        public function getAttribute($key) { return null; }
    };
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn(null);
    
    $mockRepository->shouldReceive('create')
        ->once()
        ->with(m::on(function ($data) use ($user) {
            return $data['user_id'] === $user->id &&
                   $data['type'] instanceof SubscriptionType &&
                   $data['status'] instanceof SubscriptionStatus;
        }))
        ->andReturn(new Subscription([
            'user_id' => $user->id,
            'type' => SubscriptionType::TRIAL(),
            'status' => SubscriptionStatus::ACTIVE(),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'grace_ends_at' => now()->addDays(37),
        ]));

    $service = new SubscriptionService($mockRepository);
    $subscription = $service->createTrial($user);

    expect($subscription)->toBeInstanceOf(Subscription::class);
    expect($subscription->type->getValue())->toBe(SubscriptionType::TRIAL);
    expect($subscription->status->getValue())->toBe(SubscriptionStatus::ACTIVE);
});

test('subscription service throws exception for existing active subscription', function () {
    $user = m::mock(User::class);
    $existingSubscription = m::mock(Subscription::class);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn($existingSubscription);

    $service = new SubscriptionService($mockRepository);

    expect(fn() => $service->createTrial($user))
        ->toThrow('User already has an active subscription');
});

test('subscription service checks active subscription correctly', function () {
    $user = m::mock(User::class);
    $activeSubscription = m::mock(Subscription::class);
    $activeSubscription->shouldReceive('isActive')->andReturn(true);
    $activeSubscription->shouldReceive('isInGracePeriod')->andReturn(false);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn($activeSubscription);

    $service = new SubscriptionService($mockRepository);
    $isActive = $service->checkActive($user);

    expect($isActive)->toBeTrue();
});

test('subscription service returns false for no active subscription', function () {
    $user = m::mock(User::class);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn(null);

    $service = new SubscriptionService($mockRepository);
    $isActive = $service->checkActive($user);

    expect($isActive)->toBeFalse();
});

test('subscription service can cancel subscription', function () {
    $user = new class extends User {
        public function __construct() {}
        public function getAttribute($key) { return null; }
    };
    $subscription = new class extends Subscription {
        public function getAttribute($key) { return null; }
    };
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn($subscription);
    
    $mockRepository->shouldReceive('update')
        ->once()
        ->with($subscription, [
            'status' => SubscriptionStatus::CANCELED(),
        ])
        ->andReturn(true);

    $service = new SubscriptionService($mockRepository);
    $result = $service->cancel($user);

    expect($result)->toBeTrue();
});

test('subscription service throws exception when canceling non-existent subscription', function () {
    $user = m::mock(User::class);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn(null);

    $service = new SubscriptionService($mockRepository);

    expect(fn() => $service->cancel($user))
        ->toThrow('No active subscription found');
});

test('subscription service can get current subscription', function () {
    $user = m::mock(User::class);
    $subscription = m::mock(Subscription::class);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn($subscription);

    $service = new SubscriptionService($mockRepository);
    $current = $service->getCurrent($user);

    expect($current)->toBe($subscription);
});

test('subscription service returns null for no current subscription', function () {
    $user = m::mock(User::class);
    
    $mockRepository = m::mock(SubscriptionRepositoryInterface::class);
    $mockRepository->shouldReceive('getActiveForUser')
        ->once()
        ->with($user)
        ->andReturn(null);

    $service = new SubscriptionService($mockRepository);
    $current = $service->getCurrent($user);

    expect($current)->toBeNull();
});
