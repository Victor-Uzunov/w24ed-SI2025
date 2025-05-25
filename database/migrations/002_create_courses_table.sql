-- Up
-- Creates the courses table to store course information with program relationships
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    credits INT NOT NULL CHECK (credits BETWEEN 1 AND 9),
    year_available INT NOT NULL CHECK (year_available > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Down
-- Removes the courses table
DROP TABLE IF EXISTS courses; 