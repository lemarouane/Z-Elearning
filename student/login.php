<?php
require_once '../includes/config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, is_validated FROM students WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        if ($password === $student['password']) { // Simple comparison (consider hashing later)
            if ($student['is_validated']) {
                $_SESSION['student_id'] = $student['id'];
                $conn->query("INSERT INTO activity_logs (user_id, user_role, action) VALUES ({$student['id']}, 'student', 'Logged in')");
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Your account is pending validation by an admin.";
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Username not found!";
    }
    $stmt->close();
    regenerate_csrf_token();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2>Student Login</h2>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-action login">Login</button>
        </form>
        <p>Not registered? <a href="register.php" class="btn-action register">Register</a></p>
    </div>
</body>
</html>