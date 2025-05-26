-- Up
-- Creates the programs table to store educational program information
CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    degree ENUM('bachelor', 'master') NOT NULL,
    years_to_study INT NOT NULL CHECK (years_to_study BETWEEN 3 AND 6),
    type ENUM('full-time', 'part-time', 'distance') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Down
-- Removes the programs table
DROP TABLE IF EXISTS programs; 