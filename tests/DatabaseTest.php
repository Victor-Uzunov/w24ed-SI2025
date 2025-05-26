<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Database;
use PDO;

class DatabaseTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        $config = [
            'in_memory' => true
        ];
        $this->db = Database::getInstance($config);
        
        // Create test schema
        $connection = $this->db->getConnection();
        $connection->exec("
            CREATE TABLE IF NOT EXISTS programs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                degree TEXT DEFAULT 'bachelor',
                years_to_study INTEGER DEFAULT 4
            )
        ");
    }

    protected function tearDown(): void
    {
        $connection = $this->db->getConnection();
        $connection->exec("DROP TABLE IF EXISTS programs");
    }

    public function testDatabaseConnection()
    {
        $this->assertNotNull($this->db);
        $this->assertTrue($this->db->isInMemory());
        $this->assertEquals('sqlite', $this->db->getDbType());
    }

    public function testGetConnection()
    {
        $connection = $this->db->getConnection();
        $this->assertInstanceOf(PDO::class, $connection);
    }

    public function testTransactions()
    {
        $this->db->beginTransaction();
        $connection = $this->db->getConnection();

        $stmt = $connection->prepare("INSERT INTO programs (name, type) VALUES (?, ?)");
        $result = $stmt->execute(['Test Program', 'Bachelor']);

        $this->assertTrue($result);
        $this->db->commit();

        $stmt = $connection->query("SELECT * FROM programs WHERE name = 'Test Program'");
        $program = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($program);
        $this->assertEquals('Test Program', $program['name']);
        $this->assertEquals('Bachelor', $program['type']);
    }
}
