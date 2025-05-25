<?php

class ValidationException extends Exception {
    private array $errors;

    public function __construct(array $errors) {
        parent::__construct("Validation Failed", 400);
        $this->errors = $errors;
    }

    public function getErrors(): array {
        return $this->errors;
    }
} 