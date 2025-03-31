<?php
require_once 'includes/config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isset($_SESSION['student_id'])) {
    header("Location: student/view_course.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Zouhair E-learning - Professional Online Education Platform">
    <title>Welcome - Zouhair E-learning</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
            flex: 1;
        }
        h1 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 40px;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Zouhair E-learning</h1>
        <p>Your professional online education platform for learning and growth.</p>
        <div class="buttons">
            <a href="admin/login.php" class="btn">Admin Login</a>
            <a href="student/login.php" class="btn">Student Login</a>
            <a href="student/register.php" class="btn">Register as Student</a>
        </div>
    </div>
    <footer>
        <p>© <?php echo date('Y'); ?> Zouhair E-learning. All rights reserved.</p>
    </footer>
</body>
</html>