<?php

echo "Bachelor Curriculum Manager Quick Start\n";
echo "===================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n");
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
$missing_extensions = array_filter($required_extensions, fn($ext) => !extension_loaded($ext));

if (!empty($missing_extensions)) {
    die("Error: Missing required PHP extensions: " . implode(', ', $missing_extensions) . "\n");
}

// Create necessary directories
echo "Creating project directories...\n";
$directories = ['config', 'logs'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir\n";
    }
}

// Create logs file
$log_file = 'logs/php_errors.log';
touch($log_file);
chmod($log_file, 0666);
echo "Created log file: $log_file\n";

// Get database configuration
echo "\nPlease enter your database configuration:\n";
echo "Database host (default: localhost): ";
$host = trim(fgets(STDIN)) ?: 'localhost';

echo "Database name (default: curriculum_manager): ";
$dbname = trim(fgets(STDIN)) ?: 'curriculum_manager';

echo "Database username (default: root): ";
$username = trim(fgets(STDIN)) ?: 'root';

echo "Database password: ";
$password = trim(fgets(STDIN));

// Test database connection
echo "\nTesting database connection...\n";
try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Create database configuration file
echo "\nCreating database configuration...\n";
$config = <<<PHP
<?php

return [
    'host' => '$host',
    'dbname' => '$dbname',
    'username' => '$username',
    'password' => '$password',
    'charset' => 'utf8mb4'
];
PHP;

file_put_contents('config/database.php', $config);
echo "Created config/database.php\n";

// Import database schema
echo "\nImporting database schema...\n";
try {
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    
    // Import schema
    $schema = file_get_contents(__DIR__ . '/setup_database.sql');
    $pdo->exec($schema);
    echo "Database schema imported successfully!\n";
} catch (PDOException $e) {
    die("Failed to import database schema: " . $e->getMessage() . "\n");
}

echo "\nSetup completed successfully!\n\n";
echo "To start the application:\n";
echo "1. Open a terminal in the project directory\n";
echo "2. Run: php -S localhost:8000\n";
echo "3. Open http://localhost:8000 in your web browser\n\n";

// Ask if user wants to start the server now
echo "Would you like to start the server now? (y/n): ";
$start = strtolower(trim(fgets(STDIN)));

if ($start === 'y') {
    echo "\nStarting PHP development server...\n";
    echo "Access the application at http://localhost:8000\n";
    echo "Press Ctrl+C to stop the server\n\n";
    system('php -S localhost:8000');
} 