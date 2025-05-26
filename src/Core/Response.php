<?php

namespace App\Core;

class Response
{
    private $data;
    private int $statusCode;
    private array $headers;

    public function __construct($data = null, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
        ], $headers);
    }

    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send response
        if ($this->data !== null) {
            echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
        }
    }
} 