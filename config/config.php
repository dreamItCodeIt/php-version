<?php
// Application Configuration
define('APP_NAME', 'School Results Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/php-version');

// Database Configuration
define('DB_PATH', __DIR__ . '/../database/school_results.db');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
session_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Include required files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/Subject.php';
require_once __DIR__ . '/../classes/Result.php';
require_once __DIR__ . '/../classes/DivisionCalculator.php';
?>
