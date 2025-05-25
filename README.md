# Bachelor Curriculum Manager

A web-based application for managing bachelor degree programmes and their courses. The application allows users to create and manage bachelor programmes, add courses to these programmes, and define course dependencies.

## Features

- Create, view, and delete bachelor programmes
- Manage courses within each programme
- Define course prerequisites and dependencies
- Input validation and error handling
- Modern, responsive user interface

## Technical Stack

- Backend: PHP 7.4+ (no frameworks)
- Database: MySQL 5.7+ with PDO
- Frontend: HTML5, CSS3, and vanilla JavaScript (ES6+)
- No external libraries or build tools required

## Requirements

- PHP 7.4 or higher with PDO and MySQL extensions enabled
- MySQL 5.7 or higher
- Web server (Apache/Nginx) with mod_rewrite enabled
- Modern web browser with JavaScript enabled
- Composer (optional, for development)

## Installation and Setup

### 1. Clone the Repository
```bash
git clone [repository-url]
cd bachelor-curriculum-manager
```

### 2. Database Setup

1. Create the MySQL database and user:
```sql
CREATE DATABASE curriculum_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'curriculum_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON curriculum_manager.* TO 'curriculum_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Import the database schema:
```bash
mysql -u curriculum_user -p curriculum_manager < schema.sql
```

### 3. Configuration

1. Update database credentials in `config/database.php`:
```php
return [
    'host' => 'localhost',
    'dbname' => 'curriculum_manager',
    'username' => 'curriculum_user',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```

2. Configure your web server:

For Apache, ensure the following modules are enabled:
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo service apache2 restart
```

Example Apache VirtualHost configuration:
```apache
<VirtualHost *:80>
    ServerName curriculum.local
    DocumentRoot /path/to/bachelor-curriculum-manager
    
    <Directory /path/to/bachelor-curriculum-manager>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/curriculum_error.log
    CustomLog ${APACHE_LOG_DIR}/curriculum_access.log combined
</VirtualHost>
```

### 4. File Permissions

Set appropriate permissions:
```bash
# On Linux/Unix systems
chmod -R 755 .
chmod -R 777 logs    # Create this directory for error logs
chown -R www-data:www-data .  # Use appropriate web server user
```

### 5. Running the Application

For development:
```bash
php -S localhost:8000
```

For production, access through your configured web server.

## Testing

### Manual Testing

1. Programme Management Testing:
```bash
# Create a test programme
curl -X POST http://localhost:8000/api/programmes.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Computer Science","years_to_study":3,"type":"full-time"}'

# Get all programmes
curl http://localhost:8000/api/programmes.php

# Get specific programme
curl http://localhost:8000/api/programmes.php?id=1

# Delete programme
curl -X DELETE http://localhost:8000/api/programmes.php?id=1
```

2. Course Management Testing:
```bash
# Create a course
curl -X POST "http://localhost:8000/api/courses.php?programme_id=1" \
  -H "Content-Type: application/json" \
  -d '{"name":"Programming 101","credits":6,"available_year":1,"description":"Intro to programming"}'

# Get course details
curl "http://localhost:8000/api/courses.php?programme_id=1&id=1"

# Delete course
curl -X DELETE "http://localhost:8000/api/courses.php?programme_id=1&id=1"
```

### Automated Testing

Create a new directory `tests` and add PHPUnit tests:

```bash
# Install PHPUnit (if using Composer)
composer require --dev phpunit/phpunit

# Run tests
./vendor/bin/phpunit tests
```

Example test structure:
```
tests/
├── Unit/
│   ├── ProgrammeTest.php
│   └── CourseTest.php
└── Integration/
    ├── ProgrammeApiTest.php
    └── CourseApiTest.php
```

## Known Issues and Limitations

### Security
1. No authentication/authorization system
2. CORS settings are too permissive
3. No rate limiting on API endpoints
4. No input sanitization for HTML content

### Database
1. No connection pooling for high concurrency
2. No database migration system
3. No query optimization for large datasets
4. No soft delete functionality

### Frontend
1. No loading states during API calls
2. No offline support
3. No pagination UI for large lists
4. Limited error handling for network failures

### Business Logic
1. No maximum limit on course prerequisites
2. No validation for complex circular dependencies
3. No support for course credit requirements per year
4. No validation for total programme credits

## Troubleshooting

### Common Issues

1. Database Connection Errors:
   - Verify MySQL service is running
   - Check credentials in config/database.php
   - Ensure MySQL user has correct privileges

2. API Endpoint Errors:
   - Check PHP error logs in logs/php_errors.log
   - Verify .htaccess is properly configured
   - Ensure mod_rewrite is enabled

3. Frontend Issues:
   - Clear browser cache
   - Check browser console for JavaScript errors
   - Verify Content-Security-Policy headers

### Debug Mode

To enable debug mode, modify `config/database.php`:
```php
return [
    // ... existing config ...
    'debug' => true  // Add this line
];
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new features
4. Ensure all tests pass
5. Submit a pull request

## Future Improvements

1. Add authentication and user roles
2. Implement course editing functionality
3. Add programme statistics and reporting
4. Create data export/import features
5. Add automated testing suite
6. Implement soft delete functionality
7. Add database migrations
8. Improve error handling and logging
9. Add API documentation using OpenAPI/Swagger
10. Implement frontend state management

## License

This project is licensed under the MIT License. 