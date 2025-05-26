<?php

namespace App\Repository;

use App\Database;
use App\Exception\ValidationException;
use PDO;

class CourseRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('
            SELECT c.*, p.name as program_name 
            FROM courses c
            JOIN programmes p ON p.id = c.programme_id
            ORDER BY c.name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, p.name as program_name 
            FROM courses c
            JOIN programmes p ON p.id = c.programme_id
            WHERE c.id = ?
        ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function findByProgram(int $programId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM courses 
            WHERE programme_id = ?
            ORDER BY year_available, name
        ');
        $stmt->execute([$programId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): array
    {
        $this->validateCourse($data);

        $stmt = $this->db->prepare('
            INSERT INTO courses (name, credits, year_available, programme_id)
            VALUES (:name, :credits, :year_available, :programme_id)
        ');

        $stmt->execute([
            ':name' => $data['name'],
            ':credits' => $data['credits'],
            ':year_available' => $data['year_available'],
            ':programme_id' => $data['programme_id']
        ]);

        return $this->find($this->db->lastInsertId());
    }

    public function update(int $id, array $data): array
    {
        $this->validateCourse($data);

        $stmt = $this->db->prepare('
            UPDATE courses 
            SET name = :name,
                credits = :credits,
                year_available = :year_available,
                programme_id = :programme_id
            WHERE id = :id
        ');

        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':credits' => $data['credits'],
            ':year_available' => $data['year_available'],
            ':programme_id' => $data['programme_id']
        ]);

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function validateCourse(array $data): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Името на курса е задължително';
        }

        if (!isset($data['credits']) || $data['credits'] < 1 || $data['credits'] > 30) {
            $errors[] = 'Кредитите трябва да са между 1 и 30';
        }

        if (!isset($data['year_available']) || $data['year_available'] < 1) {
            $errors[] = 'Годината на достъпност трябва да е положително число';
        }

        if (empty($data['programme_id'])) {
            $errors[] = 'Програмата е задължителна';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 