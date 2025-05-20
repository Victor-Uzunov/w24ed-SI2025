# FMI Course Program Editor

A simple web application for creating and managing university course programs at FMI. This application allows faculty administrators and program coordinators to manage courses and their basic information.

## Features

### Core Functionality
- Create and edit course programs for Bachelor's and Master's degrees
- Add, edit, and remove courses with detailed information
- Save and load program data

### Course Management
- Course name and basic information
- Credit allocation (1-30 credits)
- Semester assignment (1-8)
- Course type (Mandatory/Optional/Facultative)

### Data Management
- MySQL or SQLite database support
- Data validation and error handling
- Transaction support for data integrity

## Technical Requirements

### Backend
- PHP 7.4 or higher
- MySQL 5.7+ or SQLite3
- PHP PDO extension enabled
- PHP mysql extension (for MySQL)
- PHP sqlite3 extension (for SQLite)

### Frontend
- Modern web browser with JavaScript enabled
- No additional plugins required

## Database Setup Guide

The application supports two database systems: SQLite (for development) and MySQL (for production). Here's how to set up each:

### Option 1: SQLite (Recommended for Development)

SQLite is the default and simplest option for development. It:
- Requires no separate database server
- Stores data in a single file
- Works out of the box
- Perfect for development and testing

Setup steps:

1. Create and set permissions for the database directory:
```bash
# On Linux/Mac:
mkdir database
chmod 777 database

# On Windows (PowerShell):
New-Item -ItemType Directory -Path database
icacls database /grant Everyone:F
```

2. Verify PHP SQLite extensions:
```bash
php -m | grep sqlite
# Should show pdo_sqlite
```

3. Start the development server:
```bash
php -S localhost:8000
```

The SQLite database will be automatically created at `database/program.db` when you first use the application.

### Option 2: MySQL (Recommended for Production)

MySQL is better suited for production environments as it:
- Handles concurrent connections better
- Provides better performance for larger datasets
- Offers more advanced features
- Has better backup and maintenance tools

Setup steps:

1. Local MySQL Setup:
```sql
CREATE DATABASE fmi_courses CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fmi_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON fmi_courses.* TO 'fmi_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Docker MySQL Setup (Alternative):
```bash
# Create network
docker network create fmi-network

# Start MySQL container
docker run --name fmi-mysql \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -e MYSQL_DATABASE=fmi_courses \
  -e MYSQL_USER=fmi_user \
  -e MYSQL_PASSWORD=your_password \
  -p 3306:3306 \
  --network fmi-network \
  -d mysql:8.0
```

3. Update configuration in `config/database.php`:
```php
return [
    'default' => 'mysql',  // Change from 'sqlite' to 'mysql'
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'host' => 'localhost',  // or 'host.docker.internal' if using Docker
            'port' => '3306',
            'dbname' => 'fmi_courses',
            'user' => 'fmi_user',
            'password' => 'your_password'
        ]
    ]
];
```

4. Start the server:
```bash
php -S localhost:8000
```

### Differences Between SQLite and MySQL

| Feature | SQLite | MySQL |
|---------|--------|-------|
| Setup Complexity | Simple, no configuration needed | Requires server setup and configuration |
| Performance | Good for small-to-medium datasets | Better for large datasets and concurrent users |
| Concurrency | Limited concurrent write operations | Excellent concurrent operation handling |
| Backup | Simple file copy | Requires proper backup procedures |
| Use Case | Development, testing, small applications | Production, large applications |
| Maintenance | Minimal | Requires regular maintenance |
| Security | File-level permissions | User-based access control |

### When to Use Each

Use SQLite when:
- Developing or testing the application
- Running in a single-user environment
- Need a simple, portable solution
- Don't need advanced database features

Use MySQL when:
- Deploying to production
- Expecting multiple concurrent users
- Need better performance for large datasets
- Require advanced database features
- Need robust backup and recovery options

## Installation and Setup

1. Clone the repository:
```bash
git clone [repository-url]
cd fmi-course-program-editor
```

2. Create database directory and set permissions:
```bash
mkdir database
chmod 777 database  # On Windows, ensure the directory is writable
```

3. Database Setup:

For SQLite (Default Development Setup):
- The application will automatically create a SQLite database in the `database` directory
- No additional configuration needed

For MySQL:
1. Create a new MySQL database:
```sql
CREATE DATABASE fmi_courses CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update database configuration in `php/db.php`:
```php
$config = [
    'type' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'fmi_courses',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

4. Start the PHP development server:
```bash
php -S localhost:8000
```

5. Access the application:
- Open your web browser and navigate to `http://localhost:8000`
- The application will automatically create the necessary database tables on first run

## API Endpoints

The application provides simple REST endpoints for managing program data:

### GET /php/load_program.php
- Loads the most recent program data
- Returns JSON with program details and courses
- Example response:
```json
{
    "success": true,
    "program": {
        "name": "Computer Science",
        "type": "bachelor",
        "courses": [
            {
                "name": "Mathematics",
                "semester": 1,
                "credits": 6,
                "type": "mandatory"
            }
        ]
    }
}
```

### POST /php/save_program.php
- Saves program data
- Accepts JSON with program details and courses
- Example request body:
```json
{
    "name": "Computer Science",
    "type": "bachelor",
    "courses": [
        {
            "name": "Mathematics",
            "semester": 1,
            "credits": 6,
            "type": "mandatory"
        }
    ]
}
```
- Returns success/error status

## Usage

1. Create a New Program:
   - Click "Нова програма" or use Ctrl+N
   - Enter program name and type
   - Add courses and their details

2. Add Courses:
   - Click "Добави дисциплина"
   - Fill in course details:
     - Name (unique)
     - Semester (1-8)
     - Credits (1-30)
     - Type (Mandatory/Optional/Facultative)
   - Click "Премахни" to remove a course

3. Save/Load Programs:
   - Click "Запази" or use Ctrl+S to save
   - Click "Зареди програма" or use Ctrl+O to load

## Project Structure

```
/
├── css/
│   └── style.css          # Application styles
├── js/
│   └── app.js            # Main application logic
├── php/
│   ├── db.php           # Database configuration and utilities
│   ├── load_program.php # Program loading endpoint
│   └── save_program.php # Program saving endpoint
├── database/            # SQLite database directory
└── index.php           # Main application page
```

## Troubleshooting

1. Database Issues:
   - Ensure the `database` directory exists and is writable
   - For MySQL, verify the connection settings in `php/db.php`
   - Check PHP error logs for detailed error messages

2. Permission Issues:
   - On Linux/Mac: `chmod 777 database`
   - On Windows: Right-click > Properties > Security > Edit > Add > Everyone > Full Control

3. Server Issues:
   - Ensure PHP is installed and in your system PATH
   - Check if required PHP extensions are enabled (pdo, pdo_mysql, pdo_sqlite)
   - Verify the port 8000 is not in use by another application 