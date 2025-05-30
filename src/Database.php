<?php

namespace App;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    private $isInMemory = false;

    private function __construct($config = null) {
        if ($config && isset($config['in_memory']) && $config['in_memory']) {
            $this->pdo = new PDO('sqlite::memory:');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->isInMemory = true;
            return;
        }

        $config = require __DIR__ . '/../config/database.php';
        
        try {
            if ($config['type'] === 'sqlite') {
                $dbPath = $config['sqlite']['path'];
                $this->pdo = new PDO("sqlite:$dbPath");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->createTables();
            } else {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                    $config['mysql']['host'],
                    $config['mysql']['port'],
                    $config['mysql']['dbname']
                );
                $this->pdo = new PDO(
                    $dsn,
                    $config['mysql']['user'],
                    $config['mysql']['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            }
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance($config = null) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function isInMemory() {
        return $this->isInMemory;
    }

    public function getDbType() {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    private function createTables() {
        // Create programmes table if not exists
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS programmes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                years_to_study INTEGER NOT NULL CHECK (years_to_study BETWEEN 3 AND 6),
                type TEXT NOT NULL CHECK (type IN ("full-time", "part-time", "distance"))
            )
        ');

        // Add degree column if it doesn't exist
        try {
            $this->pdo->query('SELECT degree FROM programmes LIMIT 1');
        } catch (PDOException $e) {
            // Column doesn't exist, add it
            $this->pdo->exec('
                ALTER TABLE programmes 
                ADD COLUMN degree TEXT NOT NULL 
                CHECK (degree IN ("bachelor", "master")) 
                DEFAULT "bachelor"
            ');
        }

        // Create courses table if not exists
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                programme_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                credits INTEGER NOT NULL CHECK (credits BETWEEN 1 AND 15),
                year_available INTEGER NOT NULL,
                description TEXT,
                UNIQUE(programme_id, name),
                FOREIGN KEY (programme_id) REFERENCES programmes(id) ON DELETE CASCADE
            )
        ');

        // Create course_dependencies table if not exists
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS course_dependencies (
                course_id INTEGER NOT NULL,
                depends_on_id INTEGER NOT NULL,
                PRIMARY KEY (course_id, depends_on_id),
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (depends_on_id) REFERENCES courses(id) ON DELETE CASCADE
            )
        ');
    }
} 