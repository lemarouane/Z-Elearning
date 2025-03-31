<?php
require_once 'includes/config.php';

$admin_id = $_SESSION['admin_id'] ?? null;
if ($admin_id) {
    log_error("Admin $admin_id logged out");
}

session_unset();
session_destroy();
header("Location: index.php");
exit();
?>