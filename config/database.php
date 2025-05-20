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
            'host' => 'localhost',  // or 'host.docker.internal' if running app in Docker
            'port' => '3306',
            'dbname' => 'fmi_courses',
            'user' => 'fmi_user',
            'password' => 'your_password',  // Replace with your actual password
        ],
    ],
]; 