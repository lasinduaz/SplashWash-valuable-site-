<?php
// /application/php/config/db_connection.php
// Simple PDO DB connection. Edit credentials for your local setup.

$host = 'localhost';
$db   = 'car_wash_db';
$user = 'root'; // default for XAMPP / local setups
$pass = ''; // set your root or test password
$charset = 'utf8mb4';

// Allow overriding from environment variables when running in containers.
$host = getenv('DB_HOST') ?: $host;
$db = getenv('DB_NAME') ?: $db;
$user = getenv('DB_USER') ?: $user;
$pass = getenv('DB_PASS') ?: $pass;
$charset = getenv('DB_CHARSET') ?: $charset;

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
// PDO options enable exceptions, use native prepared statements,
// and set sensible defaults for fetch mode and charset init.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // Uncomment the next line to enable persistent connections if needed
    // PDO::ATTR_PERSISTENT         => true,
    // Ensure the connection uses the requested character set on connect
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log the detailed error for the server logs and show a safe message in production.
    error_log('DB connection failed: ' . $e->getMessage());
    $isProd = getenv('APP_ENV') === 'production' || getenv('ENV') === 'production';
    if ($isProd) {
        // Generic message for production to avoid leaking credentials/paths
        die('Database connection error.');
    }
    // In non-production environments show the detailed error for debugging
    die('DB connection failed: ' . $e->getMessage());
}
