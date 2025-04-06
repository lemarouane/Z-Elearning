<?php
require_once '../includes/config.php';

if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) 
                  VALUES ($student_id, 'student', 'Logged out', 'Student ID: $student_id')");
    session_unset();
    session_destroy();
}

header("Location: ../login.php");
exit();
?>