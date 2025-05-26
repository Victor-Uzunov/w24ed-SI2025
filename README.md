# Educational Programs Management System

A modern web application for managing educational programs and courses, built with PHP 8.1+ backend and JavaScript frontend.

## Features

- Program Management
  - Create, read, update, and delete educational programs
  - Support for different program types (full-time, part-time, distance)
  - Support for different degrees (bachelor, master)
  - Configurable study duration

- Course Management
  - Create, read, update, and delete courses within programs
  - Course dependencies management
  - Credit system support
  - Year-based course organization

- Multi-language Support
  - Full Bulgarian language interface
  - Extensible translation system

## Technical Stack

### Backend
- PHP 8.1+
- MySQL/SQLite with PDO
- RESTful API architecture
- PSR-12 coding standards
- PHPUnit for testing
- PHPStan for static analysis

### Frontend
- Vanilla JavaScript (ES6+)
- Modern CSS3
- Responsive design
- No external dependencies

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/w24ed-SI2025.git
cd w24ed-SI2025
```

2. Install dependencies:
```bash
composer install
```

3. Configure your web server:
   - Point the document root to the `public` directory
   - Ensure `.htaccess` is enabled for Apache
   - For Nginx, configure URL rewriting similar to the Apache configuration

4. Set up the database:
   - Create a new database
   - Import the schema from `database/migrations`
   - Copy `.env.example` to `.env` and configure your database connection

5. Set appropriate permissions:
```bash
chmod -R 755 public/
chmod -R 777 storage/logs/
```

## Development

### Code Style
The project follows PSR-12 coding standards. To check and fix code style:

```bash
# Check code style
composer cs

# Fix code style automatically
composer cs-fix
```

### Static Analysis
Run PHPStan for static analysis:

```bash
composer phpstan
```

### Testing
Run the test suite:

```bash
composer test
```

### All Quality Checks
Run all quality checks at once:

```bash
composer check
```

## API Documentation

### Programs Endpoints

- `GET /api/programmes` - List all programs
- `GET /api/programmes/{id}` - Get program details
- `POST /api/programmes` - Create new program
- `PUT /api/programmes/{id}` - Update program
- `DELETE /api/programmes/{id}` - Delete program

### Courses Endpoints

- `GET /api/courses` - List all courses
- `GET /api/courses/{id}` - Get course details
- `GET /api/programmes/{id}/courses` - List courses in program
- `POST /api/courses` - Create new course
- `PUT /api/courses/{id}` - Update course
- `DELETE /api/courses/{id}` - Delete course

For detailed API documentation, see the [API Documentation](docs/api.md).

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For support, please contact the development team or open an issue in the repository.