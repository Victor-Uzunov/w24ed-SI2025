<?php

class Course extends Model {
    public function create(string $programmeId, array $data): array {
        $this->validate($programmeId, $data);

        try {
            $this->beginTransaction();

            $sql = "INSERT INTO courses (programme_id, name, credits, available_year, description) 
                    VALUES (:programme_id, :name, :credits, :available_year, :description)";
            
            $this->execute($sql, [
                'programme_id' => $programmeId,
                'name' => $data['name'],
                'credits' => $data['credits'],
                'available_year' => $data['available_year'],
                'description' => $data['description'] ?? null
            ]);

            $courseId = $this->lastInsertId();

            // Add dependencies if provided
            if (!empty($data['prerequisites'])) {
                $this->addDependencies($courseId, $data['prerequisites']);
            }

            $this->commit();
            return $this->getById($programmeId, $courseId);

        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getById(string $programmeId, string $courseId): array {
        $sql = "SELECT c.*, 
                (SELECT GROUP_CONCAT(prerequisite_course_id) 
                 FROM course_dependencies 
                 WHERE course_id = c.id) as prerequisites
                FROM courses c 
                WHERE c.programme_id = :programme_id AND c.id = :id";
        
        $stmt = $this->execute($sql, [
            'programme_id' => $programmeId,
            'id' => $courseId
        ]);
        
        $course = $stmt->fetch();
        if (!$course) {
            throw new Exception("Course not found", 404);
        }

        // Convert prerequisites to array if they exist
        if ($course['prerequisites']) {
            $course['prerequisites'] = explode(',', $course['prerequisites']);
        } else {
            $course['prerequisites'] = [];
        }

        return $course;
    }

    public function delete(string $programmeId, string $courseId): void {
        $sql = "DELETE FROM courses WHERE programme_id = :programme_id AND id = :id";
        $stmt = $this->execute($sql, [
            'programme_id' => $programmeId,
            'id' => $courseId
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Course not found", 404);
        }
    }

    private function addDependencies(string $courseId, array $prerequisites): void {
        $sql = "INSERT INTO course_dependencies (course_id, prerequisite_course_id) VALUES (:course_id, :prerequisite_id)";
        
        foreach ($prerequisites as $prerequisiteId) {
            // Verify prerequisite exists and is in the same programme
            $this->validatePrerequisite($courseId, $prerequisiteId);
            
            $this->execute($sql, [
                'course_id' => $courseId,
                'prerequisite_id' => $prerequisiteId
            ]);
        }
    }

    private function validatePrerequisite(string $courseId, string $prerequisiteId): void {
        // Get programme ID of the course
        $sql = "SELECT programme_id, available_year FROM courses WHERE id = :id";
        $stmt = $this->execute($sql, ['id' => $courseId]);
        $course = $stmt->fetch();

        // Get prerequisite course details
        $stmt = $this->execute($sql, ['id' => $prerequisiteId]);
        $prerequisite = $stmt->fetch();

        if (!$prerequisite) {
            throw new Exception("Prerequisite course not found", 404);
        }

        if ($prerequisite['programme_id'] !== $course['programme_id']) {
            throw new Exception("Prerequisite course must be from the same programme", 400);
        }

        if ($prerequisite['available_year'] >= $course['available_year']) {
            throw new Exception("Prerequisite course must be from an earlier year", 400);
        }

        // Check for circular dependencies
        $this->checkCircularDependency($courseId, $prerequisiteId);
    }

    private function checkCircularDependency(string $courseId, string $prerequisiteId, array $visited = []): void {
        if (in_array($prerequisiteId, $visited)) {
            throw new Exception("Circular dependency detected", 400);
        }

        $visited[] = $prerequisiteId;

        $sql = "SELECT prerequisite_course_id FROM course_dependencies WHERE course_id = :id";
        $stmt = $this->execute($sql, ['id' => $prerequisiteId]);
        
        while ($row = $stmt->fetch()) {
            $this->checkCircularDependency($courseId, $row['prerequisite_course_id'], $visited);
        }
    }

    private function validate(string $programmeId, array $data): void {
        $errors = [];

        // Verify programme exists
        $sql = "SELECT years_to_study FROM programmes WHERE id = :id";
        $stmt = $this->execute($sql, ['id' => $programmeId]);
        $programme = $stmt->fetch();

        if (!$programme) {
            throw new Exception("Programme not found", 404);
        }

        if (empty($data['name'])) {
            $errors[] = ['field' => 'name', 'message' => 'Name is required'];
        } elseif (strlen($data['name']) < 3 || strlen($data['name']) > 150) {
            $errors[] = ['field' => 'name', 'message' => 'Name must be between 3 and 150 characters'];
        }

        if (!isset($data['credits'])) {
            $errors[] = ['field' => 'credits', 'message' => 'Credits are required'];
        } elseif ($data['credits'] < 1 || $data['credits'] > 15) {
            $errors[] = ['field' => 'credits', 'message' => 'Credits must be between 1 and 15'];
        }

        if (!isset($data['available_year'])) {
            $errors[] = ['field' => 'available_year', 'message' => 'Available year is required'];
        } elseif ($data['available_year'] < 1 || $data['available_year'] > $programme['years_to_study']) {
            $errors[] = ['field' => 'available_year', 'message' => "Available year must be between 1 and {$programme['years_to_study']}"];
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
} 