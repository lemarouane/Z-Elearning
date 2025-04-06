<?php
require_once 'includes/config.php';

$error = $message = '';
if (isset($_SESSION['student_id'])) {
    header("Location: student/dashboard.php"); // Redirect if already logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = sanitize_input($_POST['username']);
    $password = password_hash(sanitize_input($_POST['password']), PASSWORD_DEFAULT); // Hash password
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);

    // Check for duplicate username or email
    $stmt = $conn->prepare("SELECT id FROM students WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (username, password, full_name, email, phone, is_validated) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $username, $password, $full_name, $email, $phone);
        if ($stmt->execute()) {
            $message = "Registration successful! Please wait for admin validation.";
            regenerate_csrf_token();
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Zouhair E-Learning</title>
    <link rel="stylesheet" href="assets/css/admin.css"> <!-- Reuse admin CSS for consistency -->
</head>
<body class="login-page">
    <div class="login-container">
        <h2>Student Registration</h2>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
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
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn-action add">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>