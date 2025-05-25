-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS curriculum_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a user for the application
-- IMPORTANT: Change 'your_password' to a secure password
CREATE USER IF NOT EXISTS 'curriculum_user'@'localhost' IDENTIFIED BY 'your_password';

-- Grant necessary permissions
GRANT ALL PRIVILEGES ON curriculum_manager.* TO 'curriculum_user'@'localhost';
FLUSH PRIVILEGES;

-- Switch to the database
USE curriculum_manager;

-- Create tables
CREATE TABLE IF NOT EXISTS programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    years_to_study INT NOT NULL,
    type ENUM('full-time', 'part-time', 'distance') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT name_unique UNIQUE (name),
    CONSTRAINT years_range CHECK (years_to_study BETWEEN 3 AND 6)
);

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    programme_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    credits INT NOT NULL,
    available_year INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (programme_id) REFERENCES programmes(id) ON DELETE CASCADE,
    CONSTRAINT credits_range CHECK (credits BETWEEN 1 AND 15),
    CONSTRAINT unique_course_name_per_programme UNIQUE (programme_id, name)
);

CREATE TABLE IF NOT EXISTS course_dependencies (
    course_id INT NOT NULL,
    prerequisite_course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id, prerequisite_course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT no_self_dependency CHECK (course_id != prerequisite_course_id)
);

-- Create rate limiting table
CREATE TABLE IF NOT EXISTS rate_limits (
    ip_address VARCHAR(45) PRIMARY KEY,
    requests INT DEFAULT 1,
    window_start INT,
    INDEX (window_start)
); 