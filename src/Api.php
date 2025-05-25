<?php

class Api {
    private const RATE_LIMIT = 100; // requests per minute
    private const RATE_WINDOW = 60; // seconds
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function enforceRateLimit(): void {
        $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $now = time();
        $key = "rate_limit:$clientIp";

        // Create rate limit table if it doesn't exist
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                ip_address VARCHAR(45) PRIMARY KEY,
                requests INT DEFAULT 1,
                window_start INT,
                INDEX (window_start)
            )
        ");

        // Clean old records
        $this->db->exec("DELETE FROM rate_limits WHERE window_start < " . ($now - self::RATE_WINDOW));

        // Get current request count
        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (ip_address, requests, window_start)
            VALUES (:ip, 1, :now)
            ON DUPLICATE KEY UPDATE
                requests = IF(window_start < :window_start, 1, requests + 1),
                window_start = IF(window_start < :window_start, :now, window_start)
        ");

        $stmt->execute([
            'ip' => $clientIp,
            'now' => $now,
            'window_start' => $now - self::RATE_WINDOW
        ]);

        // Check if limit exceeded
        $stmt = $this->db->prepare("SELECT requests FROM rate_limits WHERE ip_address = ?");
        $stmt->execute([$clientIp]);
        $requests = $stmt->fetchColumn();

        if ($requests > self::RATE_LIMIT) {
            $this->sendError('Rate limit exceeded', [], 429);
        }
    }

    protected function validateOrigin(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = $this->getAllowedOrigins();

        if (!in_array($origin, $allowedOrigins)) {
            $this->sendError('Invalid origin', [], 403);
        }

        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }

    private function getAllowedOrigins(): array {
        // In production, this should come from configuration
        return [
            'http://localhost:8000',
            'http://localhost:3000',
            'https://your-production-domain.com'
        ];
    }

    protected function sendJson($data, int $statusCode = 200): void {
        $this->enforceRateLimit();
        $this->validateOrigin();
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        
        // Sanitize output
        $data = $this->sanitizeOutput($data);
        
        echo json_encode($data);
        exit;
    }

    protected function sendError(string $title, array $details = [], int $code = 400): void {
        $this->sendJson([
            'error' => [
                'code' => $code,
                'title' => $title,
                'details' => $details,
                'request_id' => $this->generateRequestId()
            ]
        ], $code);
    }

    private function generateRequestId(): string {
        return bin2hex(random_bytes(16));
    }

    protected function getJsonInput(): array {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Invalid JSON', [], 400);
        }

        // Sanitize input
        return $this->sanitizeInput($data);
    }

    private function sanitizeInput(array $data): array {
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                // Remove any non-printable characters
                $value = preg_replace('/[^\P{C}\n\r\t]+/u', '', $value);
                // Convert special characters to HTML entities
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        });
        return $data;
    }

    private function sanitizeOutput($data) {
        if (is_array($data)) {
            array_walk_recursive($data, function(&$value) {
                if (is_string($value)) {
                    // Decode HTML entities for output
                    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    // Remove any potentially dangerous characters
                    $value = preg_replace('/[^\P{C}\n\r\t]+/u', '', $value);
                }
            });
        }
        return $data;
    }

    protected function validateMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            $this->sendError('Method Not Allowed', [], 405);
        }
    }

    protected function validateContentType(): void {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            $this->sendError('Content-Type must be application/json', [], 415);
        }
    }
} 