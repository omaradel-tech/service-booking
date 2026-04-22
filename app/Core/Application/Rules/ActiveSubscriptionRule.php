<?php

namespace App\Core\Application\Rules;

use App\Models\User;
use App\Core\Application\Services\SubscriptionService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveSubscriptionRule implements ValidationRule
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = request()->user();

        if (!$user) {
            $fail('Authentication is required.');
            return;
        }

        if (!$this->subscriptionService->checkActive($user)) {
            $fail('An active subscription is required to perform this action.');
        }
    }
}
