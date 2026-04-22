<?php

namespace App\Modules\Cart\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use App\Core\Application\Rules\ActiveSubscriptionRule;
use App\Core\Application\Rules\NoBookingOverlapRule;
use App\Core\Application\Services\SubscriptionService;
use App\Core\Application\Contracts\BookingRepositoryInterface;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by ActiveSubscriptionRule
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'schedules' => 'required|array|min:1',
            'schedules.*.cart_item_id' => 'required|integer|exists:cart_items,id',
            'schedules.*.service_id' => 'required|integer|exists:services,id',
            'schedules.*.scheduled_at' => [
                'required',
                'date',
                'after:now',
                new NoBookingOverlapRule(app(BookingRepositoryInterface::class))
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if user has active subscription
            $subscriptionService = app(SubscriptionService::class);
            $isActive = $subscriptionService->checkActive($this->user());
            
            // Debug: Log the subscription check result
            Log::info('Checkout subscription check', [
                'user_id' => $this->user()->id,
                'is_active' => $isActive,
                'subscription' => $subscriptionService->getCurrent($this->user())
            ]);
            
            if (!$isActive) {
                $validator->errors()->add('schedules', 'An active subscription is required to perform this action.');
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'schedules.required' => 'At least one schedule is required for checkout.',
            'schedules.*.scheduled_at.after' => 'All scheduled times must be in the future.',
        ];
    }
}
