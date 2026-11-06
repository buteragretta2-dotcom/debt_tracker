<?php
// Database settings: change these to match your server.
define('DB_HOST', getenv('DT_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DT_DB_NAME') ?: 'debt_tracker');
define('DB_USER', getenv('DT_DB_USER') ?: 'root');
define('DB_PASS', getenv('DT_DB_PASS') ?: '');
define('BUSINESS_NAME', 'My Business');      // shown on receipts and reports
define('CURRENCY', 'RWF');
define('SESSION_TIMEOUT', 1800);              // seconds of inactivity before logout

date_default_timezone_set('Africa/Kigali');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed. Check config.php.');
}

session_start();
if (!empty($_SESSION['last_active']) && time() - $_SESSION['last_active'] > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_active'] = time();
