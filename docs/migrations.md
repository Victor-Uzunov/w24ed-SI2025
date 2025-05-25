# Database Migrations

This document explains how to use the database migration system in the project.

## Overview

The migration system helps manage database schema changes in a version-controlled way. Each migration file represents a specific change to the database schema and includes both "Up" (apply changes) and "Down" (reverse changes) operations.

## Migration Files

Migration files are located in the `database/migrations` directory and follow this naming convention:
```
XXX_description_of_change.sql
```
where XXX is a sequential number (e.g., 001, 002, etc.).

Each migration file has two sections:
- `-- Up`: Contains SQL statements to apply the changes
- `-- Down`: Contains SQL statements to reverse the changes

## Using the Migration CLI Tool

The migration tool is located at `bin/migrate` and supports the following commands:

### Apply Migrations
To apply all pending migrations:
```bash
php bin/migrate migrate
```

### Rollback Migrations
To rollback the most recent migration:
```bash
php bin/migrate rollback
```

To rollback multiple migrations, specify the number of steps:
```bash
php bin/migrate rollback 3  # Rolls back the last 3 migrations
```

## Current Database Structure

### Programs Table
- Stores educational program information
- Fields: id, name, degree (bachelor/master), years_to_study, type
- Includes timestamps for creation and updates

### Courses Table
- Stores course information
- Fields: id, program_id, name, credits, year_available
- Links to programs through program_id foreign key
- Includes timestamps for creation and updates

### Course Dependencies Table
- Manages course prerequisites
- Links courses through course_id and dependency_id
- Prevents self-referential dependencies
- Includes creation timestamp

## Best Practices

1. Always include both Up and Down migrations
2. Test migrations before applying to production
3. Make migrations atomic (one logical change per migration)
4. Use appropriate SQL constraints and data types
5. Include helpful comments in migration files

## Error Handling

The migration system will:
- Roll back failed migrations automatically
- Maintain database consistency using transactions
- Provide clear error messages if something goes wrong 