<?php

namespace App\Exceptions\Domain;

class BookingNotFoundException extends DomainException
{
    protected string $errorCode = 'BOOKING_NOT_FOUND';
    protected int $httpStatus = 404;

    public function __construct(string $message = 'Booking not found', array $details = [])
    {
        parent::__construct($message, $details);
    }
}
