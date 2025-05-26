<?php

namespace App\Service;

use App\Repository\ProgramRepository;
use App\Repository\CourseRepository;
use App\Exception\NotFoundException;

class ProgramService
{
    private ProgramRepository $programRepository;
    private CourseRepository $courseRepository;

    public function __construct()
    {
        $this->programRepository = new ProgramRepository();
        $this->courseRepository = new CourseRepository();
    }

    public function getAllPrograms(): array
    {
        $programs = $this->programRepository->findAll();
        return array_map([$this, 'transformProgramData'], $programs);
    }

    public function getProgram(int $id): ?array
    {
        $program = $this->programRepository->find($id);
        return $program ? $this->transformProgramData($program) : null;
    }

    public function createProgram(array $data): array
    {
        $program = $this->programRepository->create($data);
        return $this->transformProgramData($program);
    }

    public function updateProgram(int $id, array $data): array
    {
        $program = $this->programRepository->update($id, $data);
        return $this->transformProgramData($program);
    }

    public function deleteProgram(int $id): void
    {
        $this->programRepository->delete($id);
    }

    private function transformProgramData(array $program): array
    {
        // Transform degree to Bulgarian
        $degreeMap = [
            'bachelor' => 'Бакалавър',
            'master' => 'Магистър'
        ];
        $program['degree_display'] = $degreeMap[$program['degree']] ?? $program['degree'];

        // Transform type to Bulgarian
        $typeMap = [
            'full-time' => 'Редовно',
            'part-time' => 'Задочно',
            'distance' => 'Дистанционно'
        ];
        $program['type_display'] = $typeMap[$program['type']] ?? $program['type'];

        // Add total credits
        if (isset($program['courses'])) {
            $program['total_credits'] = array_sum(array_column($program['courses'], 'credits'));
        }

        return $program;
    }
} 