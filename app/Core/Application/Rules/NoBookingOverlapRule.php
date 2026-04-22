<?php

namespace App\Core\Application\Rules;

use App\Core\Application\Contracts\BookingRepositoryInterface;
use App\Modules\Service\Models\Service;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoBookingOverlapRule implements ValidationRule
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = request()->user();
        
        // Extract service_id from the schedule item path
        // $attribute will be something like "schedules.0.scheduled_at"
        $schedulePath = str_replace('.scheduled_at', '', $attribute);
        $serviceId = request()->input($schedulePath . '.service_id');
        
        $scheduledAt = Carbon::parse($value);

        if (!$user || !$serviceId) {
            $fail('Invalid booking data provided.');
            return;
        }

        $service = Service::find($serviceId);
        if (!$service) {
            $fail('Service not found.');
            return;
        }

        $endTime = $scheduledAt->copy()->addMinutes($service->duration_minutes);

        if ($this->bookingRepository->hasOverlap($user, $scheduledAt, $endTime)) {
            $fail('The selected time conflicts with an existing booking.');
        }
    }
}
