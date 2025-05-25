<?php

namespace App\Repository;

use PDO;

class ProgramRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM programs ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM programs WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function create(array $data): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO programs (name, degree, years_to_study, type)
            VALUES (:name, :degree, :years_to_study, :type)
        ');

        $stmt->execute([
            'name' => $data['name'],
            'degree' => $data['degree'],
            'years_to_study' => $data['years_to_study'],
            'type' => $data['type']
        ]);

        $id = $this->db->lastInsertId();
        return $this->find($id);
    }

    public function update(int $id, array $data): array
    {
        $stmt = $this->db->prepare('
            UPDATE programs 
            SET name = :name,
                degree = :degree,
                years_to_study = :years_to_study,
                type = :type
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'degree' => $data['degree'],
            'years_to_study' => $data['years_to_study'],
            'type' => $data['type']
        ]);

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM programs WHERE id = ?');
        $stmt->execute([$id]);
    }
} 