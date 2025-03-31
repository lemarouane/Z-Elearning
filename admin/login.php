<?php
require_once '../includes/config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$username = $password = '';

// Log page access for debugging
log_error("Admin login page accessed with CSRF token: " . $_SESSION['csrf_token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
        log_error("CSRF token mismatch. Submitted: " . ($_POST['csrf_token'] ?? 'none') . ", Expected: " . $_SESSION['csrf_token']);
    } else {
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
            log_error("Login attempt with empty fields for username: $username");
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $db_password);
                $stmt->fetch();
                if ($password === $db_password) { // Plain-text comparison
                    $_SESSION['admin_id'] = $id;
                    $conn->query("UPDATE admins SET last_login = NOW() WHERE id = $id");
                    log_error("Admin $username logged in successfully with ID $id");
                    regenerate_csrf_token(); // New token after login
                    $stmt->close();
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Incorrect username or password.";
                    log_error("Failed login attempt for username: $username - Invalid password.");
                }
            } else {
                $error = "Incorrect username or password.";
                log_error("Failed login attempt for username: $username - User not found.");
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Login - Zouhair E-learning">
    <title>Admin Login - Zouhair E-learning</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Log In</button>
        </form>
    </div>
</body>
</html>