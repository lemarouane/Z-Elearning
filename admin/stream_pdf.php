<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$file = isset($_GET['file']) ? sanitize_input($_GET['file']) : null;
$file_path = '../uploads/' . $file; // Adjust the path to properly point to uploads directory

if (!$file || !file_exists($file_path) || pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
    log_error("PDF streaming failed: File not found or invalid - $file - Admin ID: $admin_id");
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 - PDF Not Found</h1><p>The requested PDF could not be found or is invalid.</p>";
    exit();
}

$conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Streamed PDF', 'File: $file')");

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex');
header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\'');

$fp = fopen($file_path, 'rb');
fpassthru($fp);
fclose($fp);
exit();
?>