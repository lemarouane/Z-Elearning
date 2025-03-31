<?php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: view_course.php");
    exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("INSERT INTO students (name, email, phone, password, is_validated) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $name, $email, $phone, $password);
    if ($stmt->execute()) {
        $success = "Registration successful! Please wait for admin validation.";
    } else {
        $error = "Registration failed. Email may already exist.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="register-container">
        <h2>Student Registration</h2>
        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Register</button>
            <p>Already registered? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>