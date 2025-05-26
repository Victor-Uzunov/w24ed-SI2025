<?php

namespace App\Core;

use PDO;
use PDOException;

class Migration
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
    }

    public function migrate(): array
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $newMigrations = [];
        
        // Get all migration files
        $files = scandir(__DIR__ . '/../../database/migrations');
        $toApply = array_diff($files, ['.', '..', '.gitkeep']);
        
        // Sort migrations by filename
        sort($toApply);
        
        foreach ($toApply as $migration) {
            if (in_array($migration, $appliedMigrations)) {
                continue;
            }
            
            $this->db->beginTransaction();
            
            try {
                $migrationContent = file_get_contents(__DIR__ . '/../../database/migrations/' . $migration);
                $this->db->exec($migrationContent);
                
                // Record the migration
                $stmt = $this->db->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
                $stmt->execute([$migration]);
                
                $this->db->commit();
                $newMigrations[] = $migration;
                
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new \Exception("Migration failed: {$migration}\n" . $e->getMessage());
            }
        }
        
        return $newMigrations;
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration_name FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function rollback(int $steps = 1): array
    {
        $appliedMigrations = array_reverse($this->getAppliedMigrations());
        $rolledBack = [];
        
        for ($i = 0; $i < $steps && isset($appliedMigrations[$i]); $i++) {
            $migration = $appliedMigrations[$i];
            $this->db->beginTransaction();
            
            try {
                // Get the down SQL from the migration file
                $migrationContent = file_get_contents(__DIR__ . '/../../database/migrations/' . $migration);
                
                // Split the file into up and down sections if they exist
                if (preg_match('/-- Down(.*?)(?:-- Up|$)/s', $migrationContent, $matches)) {
                    $downSql = trim($matches[1]);
                    $this->db->exec($downSql);
                    
                    // Remove the migration record
                    $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration_name = ?");
                    $stmt->execute([$migration]);
                    
                    $this->db->commit();
                    $rolledBack[] = $migration;
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new \Exception("Rollback failed: {$migration}\n" . $e->getMessage());
            }
        }
        
        return $rolledBack;
    }
} 