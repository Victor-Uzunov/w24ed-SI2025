<?php

use App\Controller\ProgramController;
use App\Controller\CourseController;
use App\Core\Response;

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if exists
$basePath = '/api';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Initialize controllers
$programController = new ProgramController();
$courseController = new CourseController();

// Handle preflight requests
if ($method === 'OPTIONS') {
    $response = new Response(null, 204);
    $response->send();
    exit;
}

// Parse JSON body for POST/PUT requests
$body = [];
if (in_array($method, ['POST', 'PUT']) && !empty(file_get_contents('php://input'))) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Route the request
$response = match(true) {
    // Programs routes
    $method === 'GET' && $path === '/programmes' => $programController->index(),
    $method === 'GET' && preg_match('#^/programmes/(\d+)$#', $path, $matches) => $programController->show($matches[1]),
    $method === 'POST' && $path === '/programmes' => $programController->store($body),
    $method === 'PUT' && preg_match('#^/programmes/(\d+)$#', $path, $matches) => $programController->update($matches[1], $body),
    $method === 'DELETE' && preg_match('#^/programmes/(\d+)$#', $path, $matches) => $programController->destroy($matches[1]),

    // Courses routes
    $method === 'GET' && $path === '/courses' => $courseController->index(),
    $method === 'GET' && preg_match('#^/courses/(\d+)$#', $path, $matches) => $courseController->show($matches[1]),
    $method === 'GET' && preg_match('#^/programmes/(\d+)/courses$#', $path, $matches) => $courseController->byProgram($matches[1]),
    $method === 'POST' && $path === '/courses' => $courseController->store($body),
    $method === 'PUT' && preg_match('#^/courses/(\d+)$#', $path, $matches) => $courseController->update($matches[1], $body),
    $method === 'DELETE' && preg_match('#^/courses/(\d+)$#', $path, $matches) => $courseController->destroy($matches[1]),

    // Not found
    default => new Response(['error' => 'Not Found'], 404)
};

// Send the response
$response->send(); 