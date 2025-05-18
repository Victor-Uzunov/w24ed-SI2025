<?php

return [
    // Default to SQLite for development
    'default' => 'sqlite',

    // Database configurations
    'connections' => [
        'sqlite' => [
            'type' => 'sqlite',
            'database' => __DIR__ . '/../database/program.db',
            'in_memory' => false,
        ],
        'postgres' => [
            'type' => 'postgres',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '5432',
            'dbname' => getenv('DB_NAME') ?: 'fmi_courses',
            'user' => getenv('DB_USER') ?: 'postgres',
            'password' => getenv('DB_PASSWORD') ?: '',
        ],
    ],
]; 