<?php

namespace App\Service;

use App\Repository\CourseRepository;
use App\Repository\ProgramRepository;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;

class CourseService
{
    private CourseRepository $courseRepository;
    private ProgramRepository $programRepository;

    public function __construct(
        CourseRepository $courseRepository,
        ProgramRepository $programRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->programRepository = $programRepository;
    }

    public function getAllCourses(): array
    {
        return $this->courseRepository->findAll();
    }

    public function getCoursesByProgram(int $programId): array
    {
        $program = $this->programRepository->find($programId);
        if (!$program) {
            throw new NotFoundException('Program not found');
        }

        return $this->courseRepository->findByProgram($programId);
    }

    public function getCourse(int $courseId): array
    {
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw new NotFoundException('Course not found');
        }

        return $course;
    }

    public function createCourse(int $programId, array $data): array
    {
        // Verify program exists
        $program = $this->programRepository->find($programId);
        if (!$program) {
            throw new NotFoundException('Program not found');
        }

        return $this->courseRepository->create([
            'programme_id' => $programId,
            'name' => $data['name'],
            'credits' => (int) $data['credits'],
            'year_available' => (int) $data['year_available'],
            'depends_on' => $data['depends_on'] ?? []
        ]);
    }

    public function updateCourse(int $programId, int $courseId, array $data): array
    {
        // Verify course exists and belongs to program
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw new NotFoundException('Course not found');
        }
        if ($course['programme_id'] !== $programId) {
            throw new ValidationException(['Course does not belong to this program']);
        }

        return $this->courseRepository->update($courseId, [
            'name' => $data['name'],
            'credits' => (int) $data['credits'],
            'year_available' => (int) $data['year_available'],
            'depends_on' => $data['depends_on'] ?? []
        ]);
    }

    public function deleteCourse(int $programId, int $courseId): void
    {
        // Verify course exists and belongs to program
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw new NotFoundException('Course not found');
        }
        if ($course['programme_id'] !== $programId) {
            throw new ValidationException(['Course does not belong to this program']);
        }

        // Check if any other courses depend on this one
        $dependentCourses = $this->courseRepository->findDependentCourses($courseId);
        if (!empty($dependentCourses)) {
            throw new ValidationException([
                'Cannot delete course because other courses depend on it: ' . 
                implode(', ', array_column($dependentCourses, 'name'))
            ]);
        }

        $this->courseRepository->delete($courseId);
    }

    public function getProgramYearsToStudy(int $programId): int
    {
        $program = $this->programRepository->find($programId);
        if (!$program) {
            throw new NotFoundException('Program not found');
        }

        return (int) $program['years_to_study'];
    }

    public function hasCircularDependency(int $courseId, ?int $excludeCourseId, array $visited): bool
    {
        $dependencies = $this->courseRepository->findDependencies($courseId);
        
        foreach ($dependencies as $dependency) {
            $dependencyId = $dependency['id'];
            
            // Skip the course being updated (for update operations)
            if ($excludeCourseId !== null && $dependencyId === $excludeCourseId) {
                continue;
            }
            
            // If we've seen this course before, we have a circular dependency
            if (in_array($dependencyId, $visited)) {
                return true;
            }
            
            // Add this course to visited and check its dependencies
            $newVisited = array_merge($visited, [$dependencyId]);
            if ($this->hasCircularDependency($dependencyId, $excludeCourseId, $newVisited)) {
                return true;
            }
        }
        
        return false;
    }
} 