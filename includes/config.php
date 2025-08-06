<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_results_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'School Results Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/school-results');

// Upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Make PDO connection available globally
$GLOBALS['db'] = $pdo;

// Helper function to get database connection
function getDB() {
    return $GLOBALS['db'];
}
?>