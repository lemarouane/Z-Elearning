<?php
require_once '../includes/config.php';
check_login('admin');

$file = isset($_GET['file']) ? sanitize_input($_GET['file']) : null;
$file_path = __DIR__ . '/../' . $file;

if (!$file || !file_exists($file_path) || pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
    log_error("PDF streaming failed: File not found or invalid - $file");
    header('HTTP/1.0 404 Not Found');
    echo "PDF not found or invalid.";
    exit();
}

// Set headers to force inline display and prevent caching/downloads
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex');

// Stream the file
$fp = fopen($file_path, 'rb');
fpassthru($fp);
fclose($fp);
log_error("PDF streamed successfully: $file");
exit();