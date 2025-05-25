<?php

namespace App\Service;

use App\Repository\ProgramRepository;
use App\Exception\NotFoundException;

class ProgramService
{
    private ProgramRepository $programRepository;

    public function __construct(ProgramRepository $programRepository)
    {
        $this->programRepository = $programRepository;
    }

    public function getAllPrograms(): array
    {
        return $this->programRepository->findAll();
    }

    public function createProgram(array $data): array
    {
        return $this->programRepository->create([
            'name' => $data['name'],
            'degree' => $data['degree'],
            'years_to_study' => (int) $data['years_to_study'],
            'type' => $data['type']
        ]);
    }

    public function updateProgram(int $id, array $data): array
    {
        $program = $this->programRepository->find($id);
        if (!$program) {
            throw new NotFoundException('Program not found');
        }

        return $this->programRepository->update($id, [
            'name' => $data['name'],
            'degree' => $data['degree'],
            'years_to_study' => (int) $data['years_to_study'],
            'type' => $data['type']
        ]);
    }

    public function deleteProgram(int $id): void
    {
        $program = $this->programRepository->find($id);
        if (!$program) {
            throw new NotFoundException('Program not found');
        }

        $this->programRepository->delete($id);
    }
} 