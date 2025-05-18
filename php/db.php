<?php
class Database {
    private $db;
    private static $instance = null;
    private $dbType;
    private $inMemory;

    private function __construct($config = null) {
        $this->inMemory = false;
        
        try {
            if ($config && isset($config['type']) && $config['type'] === 'postgres') {
                $this->dbType = 'postgres';
                $connStr = sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
                    $config['host'],
                    $config['port'],
                    $config['dbname'],
                    $config['user'],
                    $config['password']
                );
                $this->db = new PDO($connStr);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    public static function getInstance($config = null) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function createTables() {
        if ($this->dbType === 'postgres') {
            // PostgreSQL tables
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS programs (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS courses (
                    id SERIAL PRIMARY KEY,
                    program_id INTEGER REFERENCES programs(id) ON DELETE CASCADE,
                    name VARCHAR(255) NOT NULL,
                    semester INTEGER NOT NULL,
                    credits INTEGER NOT NULL,
                    type VARCHAR(50) NOT NULL
                )
            ');

            $this->db->exec('
                CREATE TABLE IF NOT EXISTS dependencies (
                    id SERIAL PRIMARY KEY,
                    program_id INTEGER REFERENCES programs(id) ON DELETE CASCADE,
                    course_from VARCHAR(255) NOT NULL,
                    course_to VARCHAR(255) NOT NULL
                )
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
                    semester INTEGER NOT NULL,
                    credits INTEGER NOT NULL,
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
                    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
                )
            ');
        }
    }

    public function getConnection() {
        return $this->db;
    }

    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollBack();
    }

    public function isInMemory() {
        return $this->inMemory;
    }

    public function getDbType() {
        return $this->dbType;
    }
}
?> 