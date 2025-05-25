<?php

namespace App\Controller;

use App\Core\Response;
use App\Service\CourseService;
use App\Exception\ValidationException;

class CourseController
{
    private CourseService $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    public function index(): Response
    {
        try {
            $courses = $this->courseService->getAllCourses();
            return new Response($courses);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): Response
    {
        try {
            $course = $this->courseService->getCourse($id);
            if (!$course) {
                return new Response(['error' => 'Курсът не е намерен'], 404);
            }
            return new Response($course);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function byProgram(int $programId): Response
    {
        try {
            $courses = $this->courseService->getCoursesByProgram($programId);
            return new Response($courses);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function store(array $data): Response
    {
        try {
            $course = $this->courseService->createCourse($data);
            return new Response($course, 201);
        } catch (ValidationException $e) {
            return new Response(['errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return new Response(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id, array $data): Response
    {
        try {
            $course = $this->courseService->updateCourse($id, $data);
            return new Response($course);
        } catch (ValidationException $e) {
            return new Response(['errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return new Response(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): Response
    {
        try {
            $this->courseService->deleteCourse($id);
            return new Response(null, 204);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }
} 