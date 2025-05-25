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

    public function __construct()
    {
        $this->courseRepository = new CourseRepository();
        $this->programRepository = new ProgramRepository();
    }

    public function getAllCourses(): array
    {
        $courses = $this->courseRepository->findAll();
        return array_map([$this, 'transformCourseData'], $courses);
    }

    public function getCourse(int $id): ?array
    {
        $course = $this->courseRepository->find($id);
        return $course ? $this->transformCourseData($course) : null;
    }

    public function getCoursesByProgram(int $programId): array
    {
        $courses = $this->courseRepository->findByProgram($programId);
        return array_map([$this, 'transformCourseData'], $courses);
    }

    public function createCourse(array $data): array
    {
        // Validate that the program exists
        $program = $this->programRepository->find($data['programme_id']);
        if (!$program) {
            throw new \InvalidArgumentException('Invalid program ID');
        }

        // Validate that the year is within program's years_to_study
        if ($data['year_available'] > $program['years_to_study']) {
            throw new \InvalidArgumentException('Year available cannot be greater than program duration');
        }

        $course = $this->courseRepository->create($data);
        return $this->transformCourseData($course);
    }

    public function updateCourse(int $id, array $data): array
    {
        // Validate that the program exists if program_id is being updated
        if (isset($data['programme_id'])) {
            $program = $this->programRepository->find($data['programme_id']);
            if (!$program) {
                throw new \InvalidArgumentException('Invalid program ID');
            }

            // Validate that the year is within program's years_to_study
            if (isset($data['year_available']) && $data['year_available'] > $program['years_to_study']) {
                throw new \InvalidArgumentException('Year available cannot be greater than program duration');
            }
        }

        $course = $this->courseRepository->update($id, $data);
        return $this->transformCourseData($course);
    }

    public function deleteCourse(int $id): void
    {
        $this->courseRepository->delete($id);
    }

    private function transformCourseData(array $course): array
    {
        // Add year display
        $course['year_display'] = $course['year_available'] . ' година';
        
        // Add credits display
        $course['credits_display'] = $course['credits'] . ' кредита';

        return $course;
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