<?php

namespace App\Modules\Cart\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Application\Rules\ActiveSubscriptionRule;
use App\Core\Application\Rules\NoBookingOverlapRule;

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
                new ActiveSubscriptionRule($this->user()),
                new NoBookingOverlapRule($this->user(), $this->input('schedules.*.service_id'), $this->input('schedules.*.scheduled_at'))
            ],
        ];
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
