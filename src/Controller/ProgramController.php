<?php

namespace App\Controller;

use App\Service\ProgramService;
use App\Exception\ValidationException;

class ProgramController
{
    private ProgramService $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }

    public function list(): array
    {
        return $this->programService->getAllPrograms();
    }

    public function create(array $data): array
    {
        $this->validateProgramData($data);
        return $this->programService->createProgram($data);
    }

    public function update(int $id, array $data): array
    {
        $this->validateProgramData($data);
        return $this->programService->updateProgram($id, $data);
    }

    public function delete(int $id): void
    {
        $this->programService->deleteProgram($id);
    }

    private function validateProgramData(array $data): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Program name is required';
        }

        if (!isset($data['years_to_study']) || $data['years_to_study'] < 3 || $data['years_to_study'] > 6) {
            $errors[] = 'Years to study must be between 3 and 6';
        }

        if (!isset($data['type']) || !in_array($data['type'], ['full-time', 'part-time', 'distance'])) {
            $errors[] = 'Invalid program type';
        }

        if (!isset($data['degree']) || !in_array($data['degree'], ['bachelor', 'master'])) {
            $errors[] = 'Invalid degree type';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 