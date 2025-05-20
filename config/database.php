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
        'mysql' => [
            'type' => 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'dbname' => getenv('DB_NAME') ?: 'fmi_courses',
            'user' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
        ],
    ],
]; 