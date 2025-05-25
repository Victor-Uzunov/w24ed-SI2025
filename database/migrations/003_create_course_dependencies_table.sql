-- Up
-- Creates the course_dependencies table to manage prerequisites between courses
CREATE TABLE IF NOT EXISTS course_dependencies (
    course_id INT NOT NULL,
    dependency_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id, dependency_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (dependency_id) REFERENCES courses(id) ON DELETE CASCADE,
    CHECK (course_id != dependency_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Down
-- Removes the course_dependencies table
DROP TABLE IF EXISTS course_dependencies; 