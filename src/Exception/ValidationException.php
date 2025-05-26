<?php

namespace App\Exception;

class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(array $errors, string $message = "Validation failed", int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 