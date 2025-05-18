# FMI Course Program Editor and Dependency Visualizer

A comprehensive web application for creating, managing, and visualizing university course programs at FMI. This application allows faculty administrators and program coordinators to easily manage course dependencies and create visual representations of curriculum structures.

## Features

### Core Functionality
- Create and edit course programs for Bachelor's and Master's degrees
- Add, edit, and remove courses with detailed information
- Manage course dependencies through an interactive graph
- Export programs to PDF format
- Save and load program data
- Visualize course dependencies in an interactive graph

### Course Management
- Course name and basic information
- Credit allocation
- Semester assignment
- Course type (Mandatory/Optional/Facultative)
- Prerequisites and dependencies

### Visualization
- Interactive dependency graph with drag-and-drop support
- Visual representation of course relationships
- Semester-based course organization
- Color-coded course types
- Dynamic graph layout

### Data Management
- Flexible database support (PostgreSQL or SQLite)
- Program export to PDF
- Data validation and error handling
- Transaction support for data integrity

## Technical Requirements

### Backend
- PHP 7.4 or higher
- PostgreSQL 12+ or SQLite3
- PHP PDO extension enabled
- PHP pgsql extension (for PostgreSQL)
- PHP sqlite3 extension (for SQLite)
- Composer for dependency management

### Frontend
- Modern web browser with JavaScript enabled
- Support for SVG graphics
- No additional plugins required

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd fmi-course-program-editor
```

2. Install PHP dependencies using Composer:
```bash
composer install
```

3. Ensure proper permissions for the database directory:
```bash
chmod 777 database
```

4. Start the PHP development server:
```bash
php -S localhost:8000
```

5. Open your browser and navigate to:
```
http://localhost:8000
```

## Database Configuration

The application supports two database modes:

### SQLite Mode (Default)
No additional configuration needed. The application will automatically create and use an SQLite database in the `database` directory.

### PostgreSQL Mode
1. Install PostgreSQL 12 or higher
2. Create a new database:
```sql
CREATE DATABASE fmi_courses;
```

3. Create the required tables (run these in your PostgreSQL client):
```sql
CREATE TABLE programs (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    program_id INTEGER REFERENCES programs(id),
    name VARCHAR(255) NOT NULL,
    semester INTEGER NOT NULL,
    credits INTEGER NOT NULL,
    course_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dependencies (
    id SERIAL PRIMARY KEY,
    program_id INTEGER REFERENCES programs(id),
    course_id INTEGER REFERENCES courses(id),
    prerequisite_id INTEGER REFERENCES courses(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(course_id, prerequisite_id)
);
```

4. Configure database connection:
   Create a `config.php` file in the root directory:
```php
<?php
return [
    'db_type' => 'pgsql',  // Use 'sqlite' for SQLite mode
    'pgsql' => [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'fmi_courses',
        'user' => 'your_username',
        'password' => 'your_password'
    ]
];
```

5. Set appropriate permissions:
```bash
chmod 600 config.php
```

## Usage Guide

### Creating a New Program
1. Click "Нова програма" to start a fresh program
2. Enter the program name and select the type (Bachelor/Master)
3. Add courses using the "Добави дисциплина" button
4. For each course, specify:
   - Course name
   - Semester (1-8)
   - Credits
   - Course type

### Managing Dependencies
1. Add courses that have prerequisites
2. In the dependency graph section:
   - Drag course nodes to organize them
   - Connect prerequisites using the interface
   - Visualize the relationship between courses

### Exporting Programs
1. Complete your program structure
2. Click "Експорт" to generate a PDF
3. The PDF will include:
   - Program details
   - Course list with details
   - Dependency information

### Saving and Loading
- Use "Запази" to store your program
- Use "Зареди програма" to retrieve saved programs

## Project Structure

```
fmi-course-program-editor/
├── css/
│   └── style.css           # Main stylesheet
├── js/
│   ├── app.js             # Core application logic
│   └── graph.js           # Dependency graph visualization
├── php/
│   ├── db.php            # Database connection and schema
│   ├── save_program.php  # Save endpoint
│   ├── load_program.php  # Load endpoint
│   └── export_program.php # PDF export functionality
├── database/
│   └── program.db        # SQLite database
├── vendor/               # Composer dependencies
├── composer.json         # Dependency configuration
├── index.php            # Main entry point
└── README.md            # Documentation
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Acknowledgments

- FMI for the program structure requirements
- TCPDF for PDF generation
- PostgreSQL and SQLite for database management 