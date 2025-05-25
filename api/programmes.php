<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Model.php';
require_once __DIR__ . '/../src/Programme.php';
require_once __DIR__ . '/../src/ValidationException.php';
require_once __DIR__ . '/../src/Api.php';

class ProgrammeApi extends Api {
    private Programme $programme;

    public function __construct() {
        $this->programme = new Programme();
    }

    public function handle(): void {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getOne($_GET['id']);
                    } else {
                        $this->getAll();
                    }
                    break;

                case 'POST':
                    $this->create();
                    break;

                case 'DELETE':
                    if (!isset($_GET['id'])) {
                        $this->sendError('Programme ID is required', [], 400);
                    }
                    $this->delete($_GET['id']);
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

    private function create(): void {
        $this->validateContentType();
        $data = $this->getJsonInput();
        $programme = $this->programme->create($data);
        $this->sendJson($programme, 201);
    }

    private function getAll(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 10;
        
        $programmes = $this->programme->getAll($page, $perPage);
        $this->sendJson($programmes);
    }

    private function getOne(string $id): void {
        $programme = $this->programme->getById($id);
        $this->sendJson($programme);
    }

    private function delete(string $id): void {
        $this->programme->delete($id);
        $this->sendJson(null, 204);
    }
}

$api = new ProgrammeApi();
$api->handle(); 