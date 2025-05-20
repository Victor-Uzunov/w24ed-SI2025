<?php

header('Content-Type: application/json');
require_once 'db.php';

function validateDependencies($courses, $dependencies) {
    $courseMap = [];
    foreach ($courses as $course) {
        $courseMap[$course['name']] = $course;
    }

    foreach ($dependencies as $dep) {
        if (!isset($courseMap[$dep['from']])) {
            throw new Exception("Invalid dependency: source course '{$dep['from']}' does not exist");
        }
        if (!isset($courseMap[$dep['to']])) {
            throw new Exception("Invalid dependency: target course '{$dep['to']}' does not exist");
        }
        
        $fromSemester = $courseMap[$dep['from']]['semester'];
        $toSemester = $courseMap[$dep['to']]['semester'];
        
        if ($fromSemester >= $toSemester) {
            throw new Exception("Invalid dependency: '{$dep['from']}' (semester {$fromSemester}) cannot be a prerequisite for '{$dep['to']}' (semester {$toSemester})");
        }
    }
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Validate dependencies
    if (!empty($input['dependencies'])) {
        validateDependencies($input['courses'], $input['dependencies']);
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();
    $db->beginTransaction();
    try {
        // Insert program
        $stmt = $conn->prepare('INSERT INTO programs (name, type) VALUES (:name, :type)');
        $stmt->bindValue(':name', $input['name'], PDO::PARAM_STR);
        $stmt->bindValue(':type', $input['type'], PDO::PARAM_STR);
        $stmt->execute();
        $programId = $conn->lastInsertId();

        // Insert courses
        $stmt = $conn->prepare('
            INSERT INTO courses (program_id, name, semester, credits, type) 
            VALUES (:program_id, :name, :semester, :credits, :type)
        ');
        foreach ($input['courses'] as $course) {
            $stmt->bindValue(':program_id', $programId, PDO::PARAM_INT);
            $stmt->bindValue(':name', $course['name'], PDO::PARAM_STR);
            $stmt->bindValue(':semester', $course['semester'], PDO::PARAM_INT);
            $stmt->bindValue(':credits', $course['credits'], PDO::PARAM_INT);
            $stmt->bindValue(':type', $course['type'], PDO::PARAM_STR);
            $stmt->execute();
        }

        // Insert dependencies
        if (!empty($input['dependencies'])) {
            $stmt = $conn->prepare('
                INSERT INTO dependencies (program_id, course_from, course_to) 
                VALUES (:program_id, :course_from, :course_to)
            ');
            foreach ($input['dependencies'] as $dep) {
                $stmt->bindValue(':program_id', $programId, PDO::PARAM_INT);
                $stmt->bindValue(':course_from', $dep['from'], PDO::PARAM_STR);
                $stmt->bindValue(':course_to', $dep['to'], PDO::PARAM_STR);
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
