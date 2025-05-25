<?php

class Database {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->validateConfig();
        $this->connect();
    }

    private function validateConfig(): void {
        $required = ['host', 'dbname', 'username', 'password', 'charset'];
        $missing = array_diff($required, array_keys($this->config));
        
        if (!empty($missing)) {
            throw new Exception(
                "Missing required database configuration: " . implode(', ', $missing),
                500
            );
        }
    }

    private function connect(): void {
        try {
            // Test the connection first
            $testConnection = new PDO(
                "mysql:host={$this->config['host']};charset={$this->config['charset']}",
                $this->config['username'],
                $this->config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Check if database exists, create if it doesn't
            $dbname = $this->config['dbname'];
            $result = $testConnection->query("SHOW DATABASES LIKE '$dbname'");
            
            if ($result->rowCount() === 0) {
                $testConnection->exec("CREATE DATABASE `$dbname` CHARACTER SET {$this->config['charset']}");
            }

            // Connect to the specific database
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // Add connection pooling for production
                PDO::ATTR_PERSISTENT => !empty($this->config['persistent'])
            ]);

            // Set session SQL mode
            $this->connection->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        } catch (PDOException $e) {
            $error = $e->getMessage();
            $code = $e->getCode();

            // Map common MySQL error codes to user-friendly messages
            $errorMessages = [
                1044 => 'Database access denied',
                1045 => 'Invalid database credentials',
                1049 => 'Database does not exist',
                2002 => 'Database server is not accessible',
                2003 => 'Database server is down',
                2005 => 'Unknown database host',
                2006 => 'Database server has gone away',
                2013 => 'Lost connection to database server'
            ];

            $message = $errorMessages[$code] ?? 'Database connection error';
            throw new Exception("$message: $error", 500);
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        // Check if connection is still alive
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconnect if connection was lost
            $this->connect();
        }
        return $this->connection;
    }

    private function __clone() {}
    private function __wakeup() {}
} 