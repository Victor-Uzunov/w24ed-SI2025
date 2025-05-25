<?php

namespace App\Repository;

use PDO;

class CourseRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('
            SELECT c.*, GROUP_CONCAT(cd.dependency_id) as depends_on
            FROM courses c
            LEFT JOIN course_dependencies cd ON c.id = cd.course_id
            GROUP BY c.id
            ORDER BY c.programme_id, c.year_available, c.id
        ');
        
        return $this->processCourseResults($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByProgram(int $programId): array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, GROUP_CONCAT(cd.dependency_id) as depends_on
            FROM courses c
            LEFT JOIN course_dependencies cd ON c.id = cd.course_id
            WHERE c.programme_id = ?
            GROUP BY c.id
            ORDER BY c.year_available, c.id
        ');
        $stmt->execute([$programId]);
        
        return $this->processCourseResults($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, GROUP_CONCAT(cd.dependency_id) as depends_on
            FROM courses c
            LEFT JOIN course_dependencies cd ON c.id = cd.course_id
            WHERE c.id = ?
            GROUP BY c.id
        ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $this->processCourseResults([$result])[0] : null;
    }

    public function create(array $data): array
    {
        $this->db->beginTransaction();
        
        try {
            // Insert course
            $stmt = $this->db->prepare('
                INSERT INTO courses (programme_id, name, credits, year_available)
                VALUES (:programme_id, :name, :credits, :year_available)
            ');

            $stmt->execute([
                'programme_id' => $data['programme_id'],
                'name' => $data['name'],
                'credits' => $data['credits'],
                'year_available' => $data['year_available']
            ]);

            $courseId = $this->db->lastInsertId();

            // Insert dependencies if any
            if (!empty($data['depends_on'])) {
                $this->updateDependencies($courseId, $data['depends_on']);
            }

            $this->db->commit();
            return $this->find($courseId);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): array
    {
        $this->db->beginTransaction();
        
        try {
            // Update course
            $stmt = $this->db->prepare('
                UPDATE courses 
                SET name = :name,
                    credits = :credits,
                    year_available = :year_available
                WHERE id = :id
            ');

            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'credits' => $data['credits'],
                'year_available' => $data['year_available']
            ]);

            // Update dependencies
            $this->updateDependencies($id, $data['depends_on'] ?? []);

            $this->db->commit();
            return $this->find($id);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $this->db->beginTransaction();
        
        try {
            // Delete dependencies first
            $stmt = $this->db->prepare('DELETE FROM course_dependencies WHERE course_id = ? OR dependency_id = ?');
            $stmt->execute([$id, $id]);

            // Delete course
            $stmt = $this->db->prepare('DELETE FROM courses WHERE id = ?');
            $stmt->execute([$id]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findDependentCourses(int $courseId): array
    {
        $stmt = $this->db->prepare('
            SELECT c.*
            FROM courses c
            JOIN course_dependencies cd ON c.id = cd.course_id
            WHERE cd.dependency_id = ?
        ');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findDependencies(int $courseId): array
    {
        $stmt = $this->db->prepare('
            SELECT c.*
            FROM courses c
            JOIN course_dependencies cd ON c.id = cd.dependency_id
            WHERE cd.course_id = ?
        ');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateDependencies(int $courseId, array $dependencies): void
    {
        // Remove old dependencies
        $stmt = $this->db->prepare('DELETE FROM course_dependencies WHERE course_id = ?');
        $stmt->execute([$courseId]);

        // Add new dependencies
        if (!empty($dependencies)) {
            $stmt = $this->db->prepare('
                INSERT INTO course_dependencies (course_id, dependency_id)
                VALUES (:course_id, :dependency_id)
            ');

            foreach ($dependencies as $dependencyId) {
                $stmt->execute([
                    'course_id' => $courseId,
                    'dependency_id' => $dependencyId
                ]);
            }
        }
    }

    private function processCourseResults(array $courses): array
    {
        return array_map(function ($course) {
            if (isset($course['depends_on']) && $course['depends_on'] !== null) {
                $course['depends_on'] = array_map('intval', explode(',', $course['depends_on']));
            } else {
                $course['depends_on'] = [];
            }
            return $course;
        }, $courses);
    }
} 