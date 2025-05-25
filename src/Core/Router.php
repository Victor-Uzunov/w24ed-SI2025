<?php

namespace App\Core;

use App\Exception\NotFoundException;
use App\Exception\ValidationException;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Set default headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        try {
            $route = $this->findRoute($method, $path);
            if (!$route) {
                throw new NotFoundException('Route not found');
            }

            $params = $this->extractParams($route['path'], $path);
            $data = $this->getRequestData();

            $response = call_user_func_array($route['handler'], array_merge([$data], $params));
            
            echo json_encode([
                'success' => true,
                'data' => $response
            ]);

        } catch (ValidationException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $e->getErrors()
            ]);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]);
        }
    }

    private function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($method !== $route['method']) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $path)) {
                return $route;
            }
        }

        return null;
    }

    private function convertPathToRegex(string $path): string
    {
        return '#^' . preg_replace('/\{([^}]+)\}/', '([^/]+)', $path) . '$#';
    }

    private function extractParams(string $routePath, string $requestPath): array
    {
        $params = [];
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        foreach ($routeParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $params[$matches[1]] = $requestParts[$index];
            }
        }

        return $params;
    }

    private function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? [];
        
        return array_merge($data, $_GET, $_POST);
    }
} 