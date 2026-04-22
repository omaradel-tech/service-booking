<?php

namespace App\Core\Application\DTOs;

use App\Models\User;
use Carbon\Carbon;

class CreateBookingDTO
{
    public function __construct(
        public readonly User $user,
        public readonly int $serviceId,
        public readonly Carbon $scheduledAt
    ) {}
}
