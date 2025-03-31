<?php
require_once '../includes/config.php';
check_login('admin');

$file = isset($_GET['file']) ? sanitize_input($_GET['file']) : null;
$file_path = __DIR__ . '/../' . $file;

if (!$file || !file_exists($file_path) || pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
    header('HTTP/1.0 404 Not Found');
    echo "PDF not found.";
    exit();
}

// Set headers to display PDF inline, disable caching and downloads
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Stream the file
readfile($file_path);
exit();