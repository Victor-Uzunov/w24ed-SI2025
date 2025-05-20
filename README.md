# FMI Course Program Editor and Dependency Visualizer

A web application for creating and managing university course programs at FMI. This application allows faculty administrators and program coordinators to manage course dependencies and create visual representations of curriculum structures.

## Features

### Core Functionality
- Create and edit course programs for Bachelor's and Master's degrees
- Add, edit, and remove courses with detailed information
- Manage course dependencies through an interactive graph
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
- Zoom and pan controls

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
- Support for SVG graphics
- No additional plugins required

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd fmi-course-program-editor
```

2. Ensure proper permissions for the database directory:
```bash
chmod 777 database
```

3. Start the PHP development server:
```bash
php -S localhost:8000
```

## Database Configuration

The application supports both MySQL and SQLite databases. By default, it uses SQLite for development. To configure MySQL:

1. Open `php/db.php`
2. Update the database configuration:
```php
$config = [
    'type' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

## API Endpoints

The application provides simple REST endpoints for managing program data:

### GET /php/load_program.php
- Loads the most recent program data
- Returns JSON with program details, courses, and dependencies

### POST /php/save_program.php
- Saves program data
- Accepts JSON with program details, courses, and dependencies
- Returns success/error status

## Usage

1. Create a New Program:
   - Click "Нова програма" or use Ctrl+N
   - Enter program name and type
   - Add courses and their details

2. Add Courses:
   - Click "Добави дисциплина"
   - Fill in course details
   - Set semester and credits
   - Choose course type

3. Manage Dependencies:
   - Hold Shift and click two courses to create a dependency
   - Right-click a dependency line to remove it
   - Drag courses to rearrange the graph
   - Use zoom controls to adjust the view

4. Save/Load Programs:
   - Click "Запази" or use Ctrl+S to save
   - Click "Зареди програма" or use Ctrl+O to load

## Development

The codebase is organized as follows:

```
/
├── css/
│   └── style.css          # Application styles
├── js/
│   ├── app.js            # Main application logic
│   └── graph.js          # Dependency graph visualization
├── php/
│   ├── db.php           # Database configuration and utilities
│   ├── load_program.php # Program loading endpoint
│   └── save_program.php # Program saving endpoint
└── index.php            # Main application page
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
- MySQL and SQLite for database management 