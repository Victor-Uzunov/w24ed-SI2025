<?php

namespace App\Exception;

use Exception;

class NotFoundException extends Exception
{
    public function __construct(string $message = 'Resource not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
} 