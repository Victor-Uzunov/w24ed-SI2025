<?php

namespace App\Exception;

use Exception;

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors, int $code = 400)
    {
        parent::__construct('Validation failed', $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 