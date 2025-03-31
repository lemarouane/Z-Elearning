<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'zouhair_elearning');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("<h1>Database Connection Failed</h1><p>Error: " . $conn->connect_error . "</p>");
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("<h1>Database Error</h1><p>" . $e->getMessage() . "</p>");
}

// Session configuration
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Utility functions
function sanitize_input($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

function log_error($message) {
    $log_dir = __DIR__ . '/../logs/';
    $log_file = $log_dir . 'error_log.txt';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

function check_login($role = 'admin') {
    if ($role === 'admin' && !isset($_SESSION['admin_id'])) {
        header("Location: ../admin/login.php");
        exit();
    } elseif ($role === 'student' && !isset($_SESSION['student_id'])) {
        header("Location: ../student/login.php");
        exit();
    }
}

function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

 
?>