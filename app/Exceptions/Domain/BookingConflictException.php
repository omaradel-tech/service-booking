<?php

namespace App\Exceptions\Domain;

class BookingConflictException extends DomainException
{
    protected string $errorCode = 'BOOKING_CONFLICT';
    protected int $httpStatus = 409;

    public function __construct(string $message = 'Booking conflicts with existing schedule', array $details = [])
    {
        parent::__construct($message, $details);
    }
}
