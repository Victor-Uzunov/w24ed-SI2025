<?php

header('Content-Type: application/json');
require_once 'db.php';
try {
// Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();
    $db->beginTransaction();
    try {
    // Insert program
        $stmt = $conn->prepare('INSERT INTO programs (name, type) VALUES (:name, :type)');
        $stmt->bindValue(':name', $input['name'], SQLITE3_TEXT);
        $stmt->bindValue(':type', $input['type'], SQLITE3_TEXT);
        $stmt->execute();
        $programId = $conn->lastInsertRowID();
    // Insert courses
        $stmt = $conn->prepare('
            INSERT INTO courses (program_id, name, semester, credits, type) 
            VALUES (:program_id, :name, :semester, :credits, :type)
        ');
        foreach ($input['courses'] as $course) {
            $stmt->bindValue(':program_id', $programId, SQLITE3_INTEGER);
            $stmt->bindValue(':name', $course['name'], SQLITE3_TEXT);
            $stmt->bindValue(':semester', $course['semester'], SQLITE3_INTEGER);
            $stmt->bindValue(':credits', $course['credits'], SQLITE3_INTEGER);
            $stmt->bindValue(':type', $course['type'], SQLITE3_TEXT);
            $stmt->execute();
        }

        // Insert dependencies
        if (!empty($input['dependencies'])) {
            $stmt = $conn->prepare('
                INSERT INTO dependencies (program_id, course_from, course_to) 
                VALUES (:program_id, :course_from, :course_to)
            ');
            foreach ($input['dependencies'] as $dep) {
                $stmt->bindValue(':program_id', $programId, SQLITE3_INTEGER);
                $stmt->bindValue(':course_from', $dep['from'], SQLITE3_TEXT);
                $stmt->bindValue(':course_to', $dep['to'], SQLITE3_TEXT);
                $stmt->execute();
            }
        }

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Program saved successfully',
            'program_id' => $programId
        ]);
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
