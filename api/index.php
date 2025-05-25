<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

// Handle CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Get database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Parse the request
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', trim($uri, '/'));
    array_shift($uri); // Remove 'api' from path

    // Get JSON request body for POST requests
    $input = json_decode(file_get_contents('php://input'), true);

    // Routes
    if ($uri[0] === 'programmes') {
        if (count($uri) === 1) {
            if ($method === 'GET') {
                // GET /programmes
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare('SELECT * FROM programmes LIMIT ? OFFSET ?');
                $stmt->execute([$limit, $offset]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($method === 'POST') {
                // POST /programmes
                $stmt = $pdo->prepare('INSERT INTO programmes (name, years_to_study, type) VALUES (?, ?, ?)');
                $stmt->execute([$input['name'], $input['years_to_study'], $input['type']]);
                $input['id'] = $pdo->lastInsertId();
                http_response_code(201);
                echo json_encode($input);
            }
        } elseif (count($uri) === 2) {
            $programmeId = $uri[1];
            if ($method === 'GET') {
                // GET /programmes/{id}
                $stmt = $pdo->prepare('
                    SELECT p.*, 
                           c.id as course_id, c.name as course_name, c.credits, 
                           c.year_available, c.description
                    FROM programmes p
                    LEFT JOIN courses c ON c.programme_id = p.id
                    WHERE p.id = ?
                ');
                $stmt->execute([$programmeId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($rows)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Programme not found']);
                    exit;
                }

                $programme = [
                    'id' => $rows[0]['id'],
                    'name' => $rows[0]['name'],
                    'years_to_study' => $rows[0]['years_to_study'],
                    'type' => $rows[0]['type'],
                    'courses' => []
                ];

                foreach ($rows as $row) {
                    if ($row['course_id']) {
                        $programme['courses'][] = [
                            'id' => $row['course_id'],
                            'name' => $row['course_name'],
                            'credits' => $row['credits'],
                            'year_available' => $row['year_available'],
                            'description' => $row['description']
                        ];
                    }
                }

                echo json_encode($programme);
            } elseif ($method === 'DELETE') {
                // DELETE /programmes/{id}
                $stmt = $pdo->prepare('DELETE FROM programmes WHERE id = ?');
                $stmt->execute([$programmeId]);
                http_response_code(204);
            }
        } elseif (count($uri) === 3 && $uri[2] === 'courses') {
            $programmeId = $uri[1];
            if ($method === 'POST') {
                // POST /programmes/{id}/courses
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('
                        INSERT INTO courses (programme_id, name, credits, year_available, description)
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $programmeId,
                        $input['name'],
                        $input['credits'],
                        $input['year_available'],
                        $input['description']
                    ]);
                    $courseId = $pdo->lastInsertId();

                    // Add dependencies if any
                    if (!empty($input['depends_on'])) {
                        $stmt = $pdo->prepare('
                            INSERT INTO course_dependencies (course_id, depends_on_id)
                            VALUES (?, ?)
                        ');
                        foreach ($input['depends_on'] as $dependsOnId) {
                            $stmt->execute([$courseId, $dependsOnId]);
                        }
                    }

                    $pdo->commit();
                    $input['id'] = $courseId;
                    http_response_code(201);
                    echo json_encode($input);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            }
        } elseif (count($uri) === 4 && $uri[2] === 'courses') {
            $programmeId = $uri[1];
            $courseId = $uri[3];
            if ($method === 'GET') {
                // GET /programmes/{pid}/courses/{cid}
                $stmt = $pdo->prepare('
                    SELECT c.*,
                           GROUP_CONCAT(DISTINCT cd1.depends_on_id) as depends_on,
                           GROUP_CONCAT(DISTINCT cd2.course_id) as depended_by
                    FROM courses c
                    LEFT JOIN course_dependencies cd1 ON cd1.course_id = c.id
                    LEFT JOIN course_dependencies cd2 ON cd2.depends_on_id = c.id
                    WHERE c.id = ? AND c.programme_id = ?
                    GROUP BY c.id
                ');
                $stmt->execute([$courseId, $programmeId]);
                $course = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$course) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Course not found']);
                    exit;
                }

                $course['depends_on'] = $course['depends_on'] ? explode(',', $course['depends_on']) : [];
                $course['depended_by'] = $course['depended_by'] ? explode(',', $course['depended_by']) : [];
                echo json_encode($course);
            } elseif ($method === 'DELETE') {
                // DELETE /programmes/{pid}/courses/{cid}
                $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ? AND programme_id = ?');
                $stmt->execute([$courseId, $programmeId]);
                http_response_code(204);
            } elseif ($method === 'PUT') {
                // PUT /programmes/{pid}/courses/{cid}
                $pdo->beginTransaction();
                try {
                    // Update course basic info
                    $stmt = $pdo->prepare('
                        UPDATE courses 
                        SET name = ?, credits = ?, year_available = ?, description = ?
                        WHERE id = ? AND programme_id = ?
                    ');
                    $stmt->execute([
                        $input['name'],
                        $input['credits'],
                        $input['year_available'],
                        $input['description'],
                        $courseId,
                        $programmeId
                    ]);

                    // Delete existing dependencies
                    $stmt = $pdo->prepare('DELETE FROM course_dependencies WHERE course_id = ?');
                    $stmt->execute([$courseId]);

                    // Add new dependencies if any
                    if (!empty($input['depends_on'])) {
                        $stmt = $pdo->prepare('
                            INSERT INTO course_dependencies (course_id, depends_on_id)
                            VALUES (?, ?)
                        ');
                        foreach ($input['depends_on'] as $dependsOnId) {
                            $stmt->execute([$courseId, $dependsOnId]);
                        }
                    }

                    $pdo->commit();
                    http_response_code(200);
                    echo json_encode(['message' => 'Course updated successfully']);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            }
        }
    } elseif ($uri[0] === 'courses' && count($uri) === 1 && $method === 'GET') {
        // GET /courses - Return all courses with their program information
        $stmt = $pdo->prepare('
            SELECT c.*, p.name as programme_name, 
                   GROUP_CONCAT(DISTINCT cd.depends_on_id) as depends_on
            FROM courses c
            LEFT JOIN programmes p ON p.id = c.programme_id
            LEFT JOIN course_dependencies cd ON cd.course_id = c.id
            GROUP BY c.id
        ');
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process the depends_on field for each course
        foreach ($courses as &$course) {
            $course['depends_on'] = $course['depends_on'] ? explode(',', $course['depends_on']) : [];
        }
        
        echo json_encode($courses);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 