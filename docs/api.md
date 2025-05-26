# API Documentation

## Overview

The API follows REST principles and uses JSON for request and response bodies. All endpoints are prefixed with `/api`.

## Authentication

Currently, the API does not require authentication.

## Error Handling

Errors are returned in a consistent format:

```json
{
    "error": "Error message",
    "errors": ["Detailed error 1", "Detailed error 2"]  // For validation errors
}
```

HTTP status codes:
- 200: Success
- 201: Created
- 204: No Content
- 400: Bad Request
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Programs API

### List All Programs

```
GET /api/programmes
```

Response:
```json
[
    {
        "id": 1,
        "name": "Computer Science",
        "degree": "bachelor",
        "years_to_study": 4,
        "type": "full-time"
    }
]
```

### Get Program Details

```
GET /api/programmes/{id}
```

Response:
```json
{
    "id": 1,
    "name": "Computer Science",
    "degree": "bachelor",
    "years_to_study": 4,
    "type": "full-time"
}
```

### Create Program

```
POST /api/programmes
```

Request:
```json
{
    "name": "Computer Science",
    "degree": "bachelor",
    "years_to_study": 4,
    "type": "full-time"
}
```

Validation:
- name: required, string
- degree: required, one of ["bachelor", "master"]
- years_to_study: required, integer between 3 and 6
- type: required, one of ["full-time", "part-time", "distance"]

### Update Program

```
PUT /api/programmes/{id}
```

Request body: Same as CREATE

### Delete Program

```
DELETE /api/programmes/{id}
```

Response: 204 No Content

## Courses API

### List All Courses

```
GET /api/courses
```

Response:
```json
[
    {
        "id": 1,
        "name": "Introduction to Programming",
        "credits": 6,
        "year_available": 1,
        "programme_id": 1,
        "depends_on": []
    }
]
```

### Get Course Details

```
GET /api/courses/{id}
```

Response:
```json
{
    "id": 1,
    "name": "Introduction to Programming",
    "credits": 6,
    "year_available": 1,
    "programme_id": 1,
    "depends_on": []
}
```

### List Program Courses

```
GET /api/programmes/{id}/courses
```

Response: Array of course objects

### Create Course

```
POST /api/courses
```

Request:
```json
{
    "name": "Introduction to Programming",
    "credits": 6,
    "year_available": 1,
    "programme_id": 1,
    "depends_on": []
}
```

Validation:
- name: required, string
- credits: required, integer between 1 and 9
- year_available: required, integer between 1 and 5
- programme_id: required, must exist
- depends_on: optional, array of existing course IDs

### Update Course

```
PUT /api/courses/{id}
```

Request body: Same as CREATE

### Delete Course

```
DELETE /api/courses/{id}
```

Response: 204 No Content

## Response Format

Successful responses follow this format:

```json
{
    "data": {}, // Response data
    "message": "Success message" // Optional
}
```

## Error Format

Error responses follow this format:

```json
{
    "error": "Error message",
    "errors": [] // Optional array of validation errors
}
```

## Rate Limiting

Currently, there are no rate limits implemented.

## Versioning

The current version is considered v1 and is not explicitly stated in the URL.
Future versions will use URL versioning (e.g., `/api/v2/programmes`). 