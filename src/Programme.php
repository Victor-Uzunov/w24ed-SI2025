<?php

class Programme extends Model {
    public function create(array $data): array {
        $this->validate($data);

        $sql = "INSERT INTO programmes (name, years_to_study, type) VALUES (:name, :years_to_study, :type)";
        $this->execute($sql, [
            'name' => $data['name'],
            'years_to_study' => $data['years_to_study'],
            'type' => $data['type']
        ]);

        return $this->getById($this->lastInsertId());
    }

    public function getAll(int $page = 1, int $perPage = 10): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM programmes ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->execute($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);

        return $stmt->fetchAll();
    }

    public function getById(string $id): array {
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM courses WHERE programme_id = p.id) as course_count 
                FROM programmes p 
                WHERE p.id = :id";
        
        $stmt = $this->execute($sql, ['id' => $id]);
        $programme = $stmt->fetch();
        
        if (!$programme) {
            throw new Exception("Programme not found", 404);
        }

        return $programme;
    }

    public function delete(string $id): void {
        $sql = "DELETE FROM programmes WHERE id = :id";
        $stmt = $this->execute($sql, ['id' => $id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Programme not found", 404);
        }
    }

    private function validate(array $data): void {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = ['field' => 'name', 'message' => 'Name is required'];
        } elseif (strlen($data['name']) < 3 || strlen($data['name']) > 150) {
            $errors[] = ['field' => 'name', 'message' => 'Name must be between 3 and 150 characters'];
        }

        if (!isset($data['years_to_study'])) {
            $errors[] = ['field' => 'years_to_study', 'message' => 'Years to study is required'];
        } elseif ($data['years_to_study'] < 3 || $data['years_to_study'] > 6) {
            $errors[] = ['field' => 'years_to_study', 'message' => 'Years to study must be between 3 and 6'];
        }

        $validTypes = ['full-time', 'part-time', 'distance'];
        if (empty($data['type'])) {
            $errors[] = ['field' => 'type', 'message' => 'Type is required'];
        } elseif (!in_array($data['type'], $validTypes)) {
            $errors[] = ['field' => 'type', 'message' => 'Invalid programme type'];
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 