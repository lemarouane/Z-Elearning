<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_validated = isset($_POST['is_validated']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO students (name, email, phone, password, is_validated) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $email, $phone, $password, $is_validated);
    if ($stmt->execute()) {
        $student_id = $conn->insert_id;
        $success = "Student added successfully.";
        $conn->query("INSERT INTO activity_logs (admin_id, action) VALUES ({$_SESSION['admin_id']}, 'Added student: $student_id')");
    } else {
        $error = "Failed to add student. Email may already exist.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="dashboard-container">
        <h2>Add New Student</h2>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" style="max-width: 500px;">
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
            <div class="form-group">
                <label for="is_validated">Validated</label>
                <input type="checkbox" id="is_validated" name="is_validated">
            </div>
            <button type="submit" class="btn">Add Student</button>
            <a href="manage_students.php" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>