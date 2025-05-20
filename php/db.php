<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use Exception;

class Database
{
    private PDO $db;
    private static ?Database $instance = null;
    private string $dbType;
    private bool $inMemory;

    private function __construct(?array $config = null)
    {
        $this->inMemory = false;
        
        try {
            if ($config && isset($config['type']) && $config['type'] === 'mysql') {
                $this->dbType = 'mysql';
                $connStr = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                    $config['host'] ?? 'localhost',
                    $config['port'] ?? '3306',
                    $config['dbname']
                );
                $this->db = new PDO(
                    $connStr,
                    $config['user'] ?? 'root',
                    $config['password'] ?? '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } else {
                $this->dbType = 'sqlite';
                if ($config && isset($config['in_memory']) && $config['in_memory']) {
                    $this->db = new PDO('sqlite::memory:');
                    $this->inMemory = true;
                } else {
                    $dbPath = __DIR__ . '/../database/program.db';
                    $this->db = new PDO('sqlite:' . $dbPath);
                }
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            
            $this->createTables();
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(?array $config = null): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function createTables(): void
    {
        if ($this->dbType === 'mysql') {
            // MySQL tables
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS programs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS courses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    program_id INT,
                    name VARCHAR(255) NOT NULL,
                    semester INT NOT NULL CHECK (semester BETWEEN 1 AND 8),
                    credits INT NOT NULL CHECK (credits > 0),
                    type VARCHAR(50) NOT NULL,
                    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS dependencies (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    program_id INT,
                    course_from VARCHAR(255) NOT NULL,
                    course_to VARCHAR(255) NOT NULL,
                    UNIQUE KEY unique_dependency (program_id, course_from, course_to),
                    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        } else {
            // SQLite tables
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS programs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS courses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    program_id INTEGER,
                    name TEXT NOT NULL,
                    semester INTEGER NOT NULL CHECK (semester BETWEEN 1 AND 8),
                    credits INTEGER NOT NULL CHECK (credits > 0),
                    type TEXT NOT NULL,
                    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
                )
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS dependencies (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    program_id INTEGER,
                    course_from TEXT NOT NULL,
                    course_to TEXT NOT NULL,
                    UNIQUE(program_id, course_from, course_to),
                    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
                )
            ');
        }
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollBack();
    }

    public function isInMemory(): bool
    {
        return $this->inMemory;
    }

    public function getDbType(): string
    {
        return $this->dbType;
    }
}
