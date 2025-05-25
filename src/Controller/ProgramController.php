<?php

namespace App\Controller;

use App\Core\Response;
use App\Service\ProgramService;
use App\Exception\ValidationException;

class ProgramController
{
    private ProgramService $programService;

    public function __construct()
    {
        $this->programService = new ProgramService();
    }

    public function index(): Response
    {
        try {
            $programs = $this->programService->getAllPrograms();
            return new Response($programs);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): Response
    {
        try {
            $program = $this->programService->getProgram($id);
            if (!$program) {
                return new Response(['error' => 'Програмата не е намерена'], 404);
            }
            return new Response($program);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function store(array $data): Response
    {
        try {
            $program = $this->programService->createProgram($data);
            return new Response($program, 201);
        } catch (ValidationException $e) {
            return new Response(['errors' => $e->getErrors()], 422);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id, array $data): Response
    {
        try {
            $program = $this->programService->updateProgram($id, $data);
            return new Response($program);
        } catch (ValidationException $e) {
            return new Response(['errors' => $e->getErrors()], 422);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): Response
    {
        try {
            $this->programService->deleteProgram($id);
            return new Response(null, 204);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }
} 