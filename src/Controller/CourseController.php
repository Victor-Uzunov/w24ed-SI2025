<?php

namespace App\Controller;

use App\Service\CourseService;
use App\Exception\ValidationException;

class CourseController
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function list(int $programId = null): array
    {
        return $programId 
            ? $this->courseService->getCoursesByProgram($programId)
            : $this->courseService->getAllCourses();
    }

    public function create(int $programId, array $data): array
    {
        $this->validateCourseData($data, $programId);
        return $this->courseService->createCourse($programId, $data);
    }

    public function update(int $programId, int $courseId, array $data): array
    {
        $this->validateCourseData($data, $programId, $courseId);
        return $this->courseService->updateCourse($programId, $courseId, $data);
    }

    public function delete(int $programId, int $courseId): void
    {
        $this->courseService->deleteCourse($programId, $courseId);
    }

    private function validateCourseData(array $data, int $programId, ?int $courseId = null): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Course name is required';
        }

        if (!isset($data['credits']) || $data['credits'] < 1 || $data['credits'] > 9) {
            $errors[] = 'Credits must be between 1 and 9';
        }

        if (!isset($data['year_available']) || $data['year_available'] < 1) {
            $errors[] = 'Year available is required and must be positive';
        }

        // Validate that year_available doesn't exceed program's years_to_study
        $programYears = $this->courseService->getProgramYearsToStudy($programId);
        if ($data['year_available'] > $programYears) {
            $errors[] = "Year cannot exceed program's duration of {$programYears} years";
        }

        // Validate dependencies if present
        if (!empty($data['depends_on'])) {
            foreach ($data['depends_on'] as $dependencyId) {
                try {
                    // Check if dependency exists and is valid
                    $dependency = $this->courseService->getCourse($dependencyId);
                    
                    // Prevent self-dependency in case of update
                    if ($courseId && $dependencyId === $courseId) {
                        $errors[] = 'Course cannot depend on itself';
                        continue;
                    }

                    // Check if dependency is in the same program
                    if ($dependency['programme_id'] !== $programId) {
                        $errors[] = "Dependency course {$dependencyId} must be in the same program";
                        continue;
                    }

                    // Check if dependency is in an earlier year
                    if ($dependency['year_available'] >= $data['year_available']) {
                        $errors[] = "Dependency course {$dependencyId} must be in an earlier year";
                    }

                    // Check for circular dependencies
                    if ($this->courseService->hasCircularDependency($dependencyId, $courseId, [$dependencyId])) {
                        $errors[] = 'Circular dependency detected';
                    }
                } catch (\Exception $e) {
                    $errors[] = "Invalid dependency course ID: {$dependencyId}";
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 