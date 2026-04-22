<?php

namespace App\Exceptions\Domain;

class CartItemInvalidException extends DomainException
{
    protected string $errorCode = 'CART_ITEM_INVALID';
    protected int $httpStatus = 400;

    public function __construct(string $message = 'Cart item is invalid', array $details = [])
    {
        parent::__construct($message, $details);
    }
}
