<?php

namespace App\Repository;

use App\Database;
use App\Exception\ValidationException;
use PDO;

class ProgramRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM programmes ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT p.*, 
                   GROUP_CONCAT(c.id) as course_ids,
                   GROUP_CONCAT(c.name) as course_names,
                   GROUP_CONCAT(c.credits) as course_credits,
                   GROUP_CONCAT(c.year_available) as course_years
            FROM programmes p
            LEFT JOIN courses c ON c.programme_id = p.id
            WHERE p.id = ?
            GROUP BY p.id
        ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return null;
        }

        // Transform course data into structured array
        if ($result['course_ids']) {
            $courseIds = explode(',', $result['course_ids']);
            $courseNames = explode(',', $result['course_names']);
            $courseCredits = explode(',', $result['course_credits']);
            $courseYears = explode(',', $result['course_years']);

            $result['courses'] = array_map(
                fn($id, $name, $credits, $year) => [
                    'id' => (int)$id,
                    'name' => $name,
                    'credits' => (int)$credits,
                    'year_available' => (int)$year
                ],
                $courseIds,
                $courseNames,
                $courseCredits,
                $courseYears
            );
        } else {
            $result['courses'] = [];
        }

        // Clean up concatenated fields
        unset(
            $result['course_ids'],
            $result['course_names'],
            $result['course_credits'],
            $result['course_years']
        );

        return $result;
    }

    public function create(array $data): array
    {
        $this->validateProgram($data);

        $stmt = $this->db->prepare('
            INSERT INTO programmes (name, years_to_study, type, degree)
            VALUES (:name, :years_to_study, :type, :degree)
        ');

        $stmt->execute([
            ':name' => $data['name'],
            ':years_to_study' => $data['years_to_study'],
            ':type' => $data['type'],
            ':degree' => $data['degree'] ?? 'bachelor'
        ]);

        return $this->find($this->db->lastInsertId());
    }

    public function update(int $id, array $data): array
    {
        $this->validateProgram($data);

        $stmt = $this->db->prepare('
            UPDATE programmes 
            SET name = :name,
                years_to_study = :years_to_study,
                type = :type,
                degree = :degree
            WHERE id = :id
        ');

        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':years_to_study' => $data['years_to_study'],
            ':type' => $data['type'],
            ':degree' => $data['degree'] ?? 'bachelor'
        ]);

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $this->db->beginTransaction();
        
        try {
            // Delete associated courses first
            $stmt = $this->db->prepare('DELETE FROM courses WHERE programme_id = ?');
            $stmt->execute([$id]);

            // Delete the program
            $stmt = $this->db->prepare('DELETE FROM programmes WHERE id = ?');
            $stmt->execute([$id]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function validateProgram(array $data): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Името на програмата е задължително';
        }

        if (!isset($data['years_to_study']) || $data['years_to_study'] < 3 || $data['years_to_study'] > 6) {
            $errors[] = 'Годините на обучение трябва да са между 3 и 6';
        }

        if (empty($data['type']) || !in_array($data['type'], ['full-time', 'part-time', 'distance'])) {
            $errors[] = 'Невалиден тип на обучение';
        }

        if (!empty($data['degree']) && !in_array($data['degree'], ['bachelor', 'master'])) {
            $errors[] = 'Невалидна образователна степен';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 