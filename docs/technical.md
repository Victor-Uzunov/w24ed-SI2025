# Technical Documentation

## Application Architecture

### Overview
The application follows a client-server architecture with:
- Frontend: Vanilla JavaScript single-page application (SPA)
- Backend: PHP-based RESTful API
- Database: SQLite/MySQL with PDO abstraction

## Frontend Architecture

### Core Components (`js/app.js`)

#### ProgramEditor Class
The main application class that handles:
- UI initialization
- Event binding
- API communication
- State management

```javascript
class ProgramEditor {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.currentProgramId = null;
        this.loadPrograms();
        this.loadAllCourses();
    }
}
```

### UI Components

1. **Tabs System**
   - Programs Tab
   - Courses Tab
   - Implemented through CSS classes and JavaScript event handlers

2. **Dialog System**
   - Program Creation/Edit Dialog
   - Course Creation/Edit Dialog
   - Modal implementation with backdrop

3. **List Views**
   - Programs List
   - Courses List (grouped by program)
   - Animated transitions for CRUD operations

### API Communication

All API calls use the Fetch API:

1. **Programs Endpoints**
```javascript
// List Programs
fetch('/api/programmes')

// Create Program
fetch('/api/programmes', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(programData)
})

// Update Program
fetch(`/api/programmes/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(programData)
})

// Delete Program
fetch(`/api/programmes/${id}`, { method: 'DELETE' })
```

2. **Courses Endpoints**
```javascript
// List Courses
fetch('/api/courses')

// Create Course
fetch('/api/courses', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(courseData)
})
```

## Backend Architecture

### Entry Points

1. **public/index.php**
   - Application bootstrap
   - Environment setup
   - CORS configuration
   - Route loading

2. **src/routes.php**
   - RESTful route definitions
   - Controller method mapping
   - Request method handling

### Core Components

1. **Database Class** (`src/Database.php`)
   - Singleton pattern implementation
   - PDO connection management
   - Transaction support
   - Schema management
   ```php
   class Database {
       private static $instance = null;
       private $pdo;
       
       public static function getInstance($config = null) {
           if (self::$instance === null) {
               self::$instance = new self($config);
           }
           return self::$instance;
       }
   }
   ```

2. **Controllers**
   - ProgramController: Program CRUD operations
   - CourseController: Course CRUD operations
   - Input validation
   - Response formatting

3. **Services**
   - Business logic layer
   - Transaction management
   - Data validation

4. **Repositories**
   - Database interaction
   - Query building
   - Data mapping

### Database Schema

1. **Programs Table**
```sql
CREATE TABLE programmes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    years_to_study INTEGER NOT NULL CHECK (years_to_study BETWEEN 3 AND 6),
    type TEXT NOT NULL CHECK (type IN ("full-time", "part-time", "distance")),
    degree TEXT NOT NULL CHECK (degree IN ("bachelor", "master")) DEFAULT "bachelor"
)
```

2. **Courses Table**
```sql
CREATE TABLE courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    programme_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    credits INTEGER NOT NULL CHECK (credits BETWEEN 1 AND 15),
    year_available INTEGER NOT NULL,
    description TEXT,
    UNIQUE(programme_id, name),
    FOREIGN KEY (programme_id) REFERENCES programmes(id) ON DELETE CASCADE
)
```

3. **Course Dependencies Table**
```sql
CREATE TABLE course_dependencies (
    course_id INTEGER NOT NULL,
    depends_on_id INTEGER NOT NULL,
    PRIMARY KEY (course_id, depends_on_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_id) REFERENCES courses(id) ON DELETE CASCADE
)
```

## Development Tools and Dependencies

### PHP Dependencies

1. **PHPUnit (^9.0)**
   - Unit testing framework
   - In-memory SQLite testing
   - Test coverage reporting

2. **PHPStan (^1.10)**
   - Static analysis tool
   - Type checking
   - Code quality enforcement

3. **PHP_CodeSniffer (^3.7)**
   - Code style checking (PSR-12)
   - Automatic code formatting

### Development Workflow

1. **Code Quality**
```bash
# Check code style
composer cs

# Fix code style
composer cs-fix

# Run static analysis
composer phpstan

# Run tests
composer test

# Run all checks
composer check
```

2. **Database Management**
   - Migrations in `database/migrations/`
   - Schema versioning
   - Data seeding (if needed)

## Request Flow

1. **User Action**
   - User interacts with UI
   - JavaScript event handler triggered

2. **Frontend Processing**
   - Data validation
   - UI state update
   - API request preparation

3. **API Request**
   - Fetch API call
   - JSON data formatting
   - Error handling setup

4. **Backend Processing**
   - Route matching (`routes.php`)
   - Controller method execution
   - Service layer business logic
   - Repository data access
   - Database transaction
   - Response formatting

5. **Frontend Update**
   - Response processing
   - UI update
   - Error handling
   - User feedback

## Error Handling

1. **Frontend**
   - Input validation
   - API error handling
   - User-friendly messages
   - Network error recovery

2. **Backend**
   - ValidationException
   - Database error handling
   - Transaction rollback
   - Standardized error responses

## Security Considerations

1. **Input Validation**
   - Frontend validation
   - Backend validation
   - SQL injection prevention
   - XSS prevention

2. **Database**
   - Prepared statements
   - Transaction integrity
   - Constraint enforcement
   - Cascade deletes

3. **API**
   - CORS configuration
   - Input sanitization
   - Error message security
   - Rate limiting (planned)

## Future Enhancements

1. **Authentication/Authorization**
   - User management
   - Role-based access
   - Session handling

2. **API Improvements**
   - API versioning
   - Rate limiting
   - Caching
   - Documentation (OpenAPI/Swagger)

3. **Frontend Enhancements**
   - Responsive design improvements
   - Accessibility features
   - Performance optimizations
   - Progressive Web App features 