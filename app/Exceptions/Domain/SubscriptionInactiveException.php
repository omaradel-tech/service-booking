<?php

namespace App\Exceptions\Domain;

class SubscriptionInactiveException extends DomainException
{
    protected string $errorCode = 'SUBSCRIPTION_INACTIVE';
    protected int $httpStatus = 403;

    public function __construct(string $message = 'Subscription is not active', array $details = [])
    {
        parent::__construct($message, $details);
    }
}
