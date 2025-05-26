<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Disable foreign key checks temporarily
        $pdo->exec('PRAGMA foreign_keys = OFF');

        // Delete all records from each table
        $pdo->exec('DELETE FROM course_dependencies');
        $pdo->exec('DELETE FROM courses');
        $pdo->exec('DELETE FROM programmes');

        // Reset auto-increment counters
        $pdo->exec('DELETE FROM sqlite_sequence WHERE name IN ("course_dependencies", "courses", "programmes")');

        // Re-enable foreign key checks
        $pdo->exec('PRAGMA foreign_keys = ON');

        // Commit transaction
        $pdo->commit();

        echo "Successfully cleaned up all records from the database.\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 