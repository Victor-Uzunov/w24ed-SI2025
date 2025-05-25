<?php

function checkRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'Apache Mod Rewrite' => function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : true
    ];

    $allMet = true;
    foreach ($requirements as $requirement => $met) {
        echo $requirement . ': ' . ($met ? '✓' : '✗') . PHP_EOL;
        if (!$met) $allMet = false;
    }
    return $allMet;
}

function createConfigFile($host, $dbname, $username, $password) {
    $configContent = <<<PHP
<?php

return [
    'host' => '$host',
    'dbname' => '$dbname',
    'username' => '$username',
    'password' => '$password',
    'charset' => 'utf8mb4'
];
PHP;

    $configPath = __DIR__ . '/../config/database.php';
    if (!is_dir(dirname($configPath))) {
        mkdir(dirname($configPath), 0755, true);
    }
    file_put_contents($configPath, $configContent);
}

function createLogsDirectory() {
    $logsPath = __DIR__ . '/../logs';
    if (!is_dir($logsPath)) {
        mkdir($logsPath, 0777, true);
    }
    touch($logsPath . '/php_errors.log');
    chmod($logsPath . '/php_errors.log', 0666);
}

function testDatabaseConnection($host, $username, $password, $dbname) {
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (PDOException $e) {
        echo 'Database connection failed: ' . $e->getMessage() . PHP_EOL;
        return false;
    }
}

// Main setup script
echo "Bachelor Curriculum Manager Setup\n";
echo "================================\n\n";

echo "Checking requirements...\n";
if (!checkRequirements()) {
    echo "\nPlease install missing requirements before continuing.\n";
    exit(1);
}

echo "\nAll requirements met! Proceeding with setup...\n\n";

// Get database configuration
echo "Please enter your database configuration:\n";
echo "Database host (default: localhost): ";
$host = trim(fgets(STDIN)) ?: 'localhost';

echo "Database name (default: curriculum_manager): ";
$dbname = trim(fgets(STDIN)) ?: 'curriculum_manager';

echo "Database username: ";
$username = trim(fgets(STDIN));

echo "Database password: ";
$password = trim(fgets(STDIN));

// Test database connection
echo "\nTesting database connection...\n";
if (!testDatabaseConnection($host, $username, $password, $dbname)) {
    echo "Setup failed. Please check your database credentials and try again.\n";
    exit(1);
}

// Create configuration
echo "Creating configuration file...\n";
createConfigFile($host, $dbname, $username, $password);

// Create logs directory
echo "Creating logs directory...\n";
createLogsDirectory();

echo "\nSetup completed successfully!\n";
echo "\nNext steps:\n";
echo "1. Import the database schema: mysql -u $username -p $dbname < setup/setup_database.sql\n";
echo "2. Configure your web server to point to the project directory\n";
echo "3. Make sure the web server has write permissions to the logs directory\n";
echo "4. Access the application through your web browser\n"; 