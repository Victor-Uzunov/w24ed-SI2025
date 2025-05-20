<?php

header('Content-Type: application/json');
require_once 'db.php';
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    // Get the latest program (we can extend this later to load specific programs)
    $result = $conn->query('
        SELECT id, name, type 
        FROM programs 
        ORDER BY created_at DESC 
        LIMIT 1
    ');
    $program = $result->fetch(PDO::FETCH_ASSOC);
    if (!$program) {
        throw new Exception('No program found');
    }

    // Get courses
    $stmt = $conn->prepare('
        SELECT name, semester, credits, type 
        FROM courses 
        WHERE program_id = :program_id
    ');
    $stmt->bindValue(':program_id', $program['id'], PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get dependencies
    $stmt = $conn->prepare('
        SELECT course_from, course_to 
        FROM dependencies 
        WHERE program_id = :program_id
    ');
    $stmt->bindValue(':program_id', $program['id'], PDO::PARAM_INT);
    $stmt->execute();
    $dependencies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dependencies[] = [
            'from' => $row['course_from'],
            'to' => $row['course_to']
        ];
    }

    echo json_encode([
        'success' => true,
        'program' => [
            'name' => $program['name'],
            'type' => $program['type'],
            'courses' => $courses,
            'dependencies' => $dependencies
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
