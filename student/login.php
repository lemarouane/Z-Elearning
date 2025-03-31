<?php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: view_course.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, is_validated FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
        if ($password === $student['password']) {
            if ($student['is_validated']) {
                $_SESSION['student_id'] = $student['id'];
                header("Location: view_course.php");
                exit();
            } else {
                $error = "Account not validated yet.";
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="login-container">
        <h2>Student Login</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
            <p>Not registered? <a href="register.php">Register here</a>.</p>
        </form>
    </div>
</body>
</html>