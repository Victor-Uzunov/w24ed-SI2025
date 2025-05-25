# Bachelor Curriculum Manager

A web-based application for managing bachelor degree programmes and their courses. The application allows users to create and manage bachelor programmes, add courses to these programmes, and define course dependencies.

## Features

- Create, view, and delete bachelor programmes
- Manage courses within each programme
- Define course prerequisites and dependencies
- Input validation and error handling
- Modern, responsive user interface

## Requirements

- PHP 7.4 or higher with extensions:
  - PDO MySQL
  - JSON
- MySQL 5.7 or higher
- Modern web browser with JavaScript enabled

## Quick Start Guide

### 1. Install Required Software

#### Windows:
1. Install PHP:
   - Download PHP from [windows.php.net](https://windows.php.net/download/)
   - Extract to `C:\php`
   - Add `C:\php` to your PATH environment variable
   - Copy `php.ini-development` to `php.ini`
   - Enable required extensions in php.ini:
     - extension=pdo_mysql
     - extension=json

2. Install MySQL:
   - Download MySQL Installer from [mysql.com](https://dev.mysql.com/downloads/installer/)
   - Run the installer and follow the setup wizard
   - Remember your root password!

#### Linux (Ubuntu/Debian):
```bash
sudo apt update
sudo apt install php php-mysql php-json mysql-server
```

#### macOS:
```bash
# Using Homebrew
brew install php mysql
brew services start mysql
```

### 2. Clone and Setup Project

```bash
# Clone the repository
git clone [repository-url]
cd bachelor-curriculum-manager

# Create necessary directories
mkdir config logs
chmod 777 logs  # On Unix-like systems only

# Run the setup script
php setup/setup.php
```

### 3. Database Setup

1. Log in to MySQL:
```bash
# Windows
mysql -u root -p

# Linux/macOS (if no password set)
sudo mysql
```

2. Run the database setup:
```sql
-- Inside MySQL prompt
source setup/setup_database.sql;
```

Or from terminal:
```bash
mysql -u root -p < setup/setup_database.sql
```

### 4. Start the Application

```bash
# Start PHP's built-in server
php -S localhost:8000
```

Access the application at: http://localhost:8000

## Using the Application

### Managing Programmes

1. Click "New Programme" to create a programme
2. Fill in the required details:
   - Name (3-150 characters)
   - Years to study (3-6 years)
   - Type (full-time/part-time/distance)
3. Click on a programme to view its courses
4. Use the delete button to remove a programme

### Managing Courses

1. Select a programme first
2. Click "New Course" to add a course
3. Fill in course details:
   - Name (3-150 characters)
   - Credits (1-15)
   - Available year
   - Description (optional)
   - Prerequisites (from earlier years)
4. Use the delete button to remove a course

## API Documentation

### Programmes API

#### Get All Programmes
```bash
curl http://localhost:8000/api/programmes.php
```

#### Get Single Programme
```bash
curl http://localhost:8000/api/programmes.php?id=1
```

#### Create Programme
```bash
curl -X POST http://localhost:8000/api/programmes.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Computer Science","years_to_study":3,"type":"full-time"}'
```

#### Delete Programme
```bash
curl -X DELETE http://localhost:8000/api/programmes.php?id=1
```

### Courses API

#### Get Course
```bash
curl http://localhost:8000/api/courses.php?programme_id=1&id=1
```

#### Create Course
```bash
curl -X POST http://localhost:8000/api/courses.php?programme_id=1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Programming 101",
    "credits": 6,
    "available_year": 1,
    "description": "Introduction to programming"
  }'
```

#### Delete Course
```bash
curl -X DELETE http://localhost:8000/api/courses.php?programme_id=1&id=1
```

## Troubleshooting

### Common Issues

1. Database Connection Errors:
   - Verify MySQL is running
   - Check credentials in config/database.php
   - Ensure MySQL user has correct privileges

2. Port Already in Use:
   ```bash
   # Try a different port
   php -S localhost:8001
   ```

3. Permission Issues:
   ```bash
   # On Unix-like systems
   chmod -R 755 .
   chmod -R 777 logs
   ```

4. PHP Extensions:
   - Check php.ini configuration
   - Verify required extensions are enabled
   - Run `php -m` to list loaded extensions

### Debug Mode

To enable debug mode, modify `config/database.php`:
```php
return [
    // ... existing config ...
    'debug' => true
];
```

## Development

### Project Structure
```
.
├── api/                  # API endpoints
├── config/              # Configuration files
├── css/                 # Stylesheets
├── js/                  # JavaScript modules
├── logs/               # Application logs
├── setup/              # Setup scripts
├── src/                # PHP source files
├── .htaccess          # URL rewriting rules
├── index.html         # Main application page
└── README.md          # This file
```

### Database Schema

1. Programmes Table:
   - id (INT, AUTO_INCREMENT)
   - name (VARCHAR(150), UNIQUE)
   - years_to_study (INT, 3-6)
   - type (ENUM: full-time, part-time, distance)
   - created_at (TIMESTAMP)

2. Courses Table:
   - id (INT, AUTO_INCREMENT)
   - programme_id (INT, FK)
   - name (VARCHAR(150))
   - credits (INT, 1-15)
   - available_year (INT)
   - description (TEXT)
   - created_at (TIMESTAMP)

3. Course Dependencies Table:
   - course_id (INT, FK)
   - prerequisite_course_id (INT, FK)
   - created_at (TIMESTAMP)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new features
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the MIT License. 