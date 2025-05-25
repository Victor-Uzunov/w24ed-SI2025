<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Controller\ProgramController;
use App\Controller\CourseController;
use App\Service\ProgramService;
use App\Service\CourseService;
use App\Repository\ProgramRepository;
use App\Repository\CourseRepository;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize database connection
$db = Database::getInstance()->getConnection();

// Initialize repositories
$programRepository = new ProgramRepository($db);
$courseRepository = new CourseRepository($db);

// Initialize services
$programService = new ProgramService($programRepository);
$courseService = new CourseService($courseRepository, $programRepository);

// Initialize controllers
$programController = new ProgramController($programService);
$courseController = new CourseController($courseService);

// Initialize router
$router = new Router();

// Program routes
$router->get('/api/programmes', [$programController, 'list']);
$router->post('/api/programmes', [$programController, 'create']);
$router->get('/api/programmes/{id}', [$programController, 'get']);
$router->put('/api/programmes/{id}', [$programController, 'update']);
$router->delete('/api/programmes/{id}', [$programController, 'delete']);

// Course routes
$router->get('/api/courses', [$courseController, 'list']);
$router->get('/api/programmes/{programId}/courses', [$courseController, 'list']);
$router->post('/api/programmes/{programId}/courses', [$courseController, 'create']);
$router->get('/api/programmes/{programId}/courses/{id}', [$courseController, 'get']);
$router->put('/api/programmes/{programId}/courses/{id}', [$courseController, 'update']);
$router->delete('/api/programmes/{programId}/courses/{id}', [$courseController, 'delete']);

// Handle the request
$router->handleRequest(); 