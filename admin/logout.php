<?php
require_once '../includes/config.php';

$admin_id = $_SESSION['admin_id'] ?? null;
if ($admin_id) {
    $conn->query("INSERT INTO activity_logs (user_id, user_role, action) VALUES ($admin_id, 'admin', 'Logged out')");
    log_error("Admin $admin_id logged out successfully");
}

session_unset();
session_destroy();
header("Location: login.php"); // Redirect to login instead of index.php for admin context
exit();
?>