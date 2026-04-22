<?php

namespace App\Exceptions\Domain;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class DomainException extends Exception
{
    protected int $httpStatus = 400;
    protected string $errorCode;
    protected array $details = [];

    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message);
        $this->details = $details;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode ?? static::class;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'details' => $this->getDetails(),
            ],
        ], $this->getHttpStatus());
    }
}
