CREATE TABLE programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    years_to_study TINYINT UNSIGNED NOT NULL CHECK (years_to_study BETWEEN 3 AND 6),
    type ENUM('full-time','part-time','distance') NOT NULL,
    degree ENUM('bachelor','master') NOT NULL DEFAULT 'bachelor'
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    programme_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL CHECK (credits BETWEEN 1 AND 15),
    year_available TINYINT UNSIGNED NOT NULL,
    description TEXT,
    UNIQUE KEY uk_course_name (programme_id, name),
    CONSTRAINT fk_course_programme
        FOREIGN KEY (programme_id) REFERENCES programmes(id)
        ON DELETE CASCADE
);

CREATE TABLE course_dependencies (
    course_id INT NOT NULL,
    depends_on_id INT NOT NULL,
    PRIMARY KEY (course_id, depends_on_id),
    CONSTRAINT fk_dep_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_dep_target
        FOREIGN KEY (depends_on_id) REFERENCES courses(id)
        ON DELETE CASCADE
);

-- Migration for existing databases
-- Only run this if you're updating an existing database
-- DELIMITER //
-- CREATE PROCEDURE add_degree_column()
-- BEGIN
--     IF NOT EXISTS (
--         SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
--         WHERE TABLE_NAME = 'programmes' 
--         AND COLUMN_NAME = 'degree'
--     ) THEN
--         ALTER TABLE programmes 
--         ADD COLUMN degree ENUM('bachelor','master') NOT NULL DEFAULT 'bachelor';
--     END IF;
-- END //
-- DELIMITER ;
-- CALL add_degree_column();
-- DROP PROCEDURE IF EXISTS add_degree_column; 