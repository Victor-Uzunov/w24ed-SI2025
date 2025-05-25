<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Model.php';
require_once __DIR__ . '/../src/Course.php';
require_once __DIR__ . '/../src/ValidationException.php';
require_once __DIR__ . '/../src/Api.php';

class CourseApi extends Api {
    private Course $course;

    public function __construct() {
        $this->course = new Course();
    }

    public function handle(): void {
        try {
            if (!isset($_GET['programme_id'])) {
                $this->sendError('Programme ID is required', [], 400);
            }

            $programmeId = $_GET['programme_id'];

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getOne($programmeId, $_GET['id']);
                    } else {
                        $this->sendError('Course ID is required', [], 400);
                    }
                    break;

                case 'POST':
                    $this->create($programmeId);
                    break;

                case 'DELETE':
                    if (!isset($_GET['id'])) {
                        $this->sendError('Course ID is required', [], 400);
                    }
                    $this->delete($programmeId, $_GET['id']);
                    break;

                default:
                    $this->sendError('Method Not Allowed', [], 405);
            }
        } catch (ValidationException $e) {
            $this->sendError('Validation Failed', $e->getErrors(), 400);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    private function create(string $programmeId): void {
        $this->validateContentType();
        $data = $this->getJsonInput();
        $course = $this->course->create($programmeId, $data);
        $this->sendJson($course, 201);
    }

    private function getOne(string $programmeId, string $id): void {
        $course = $this->course->getById($programmeId, $id);
        $this->sendJson($course);
    }

    private function delete(string $programmeId, string $id): void {
        $this->course->delete($programmeId, $id);
        $this->sendJson(null, 204);
    }
}

$api = new CourseApi();
$api->handle(); 