<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$student_id = $_GET['id'] ?? 0;
$message = $error = '';

$stmt = $conn->prepare("SELECT full_name, email, phone, is_validated, device_token FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: manage_students.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $is_validated = isset($_POST['is_validated']) ? 1 : 0;
    $device_token = sanitize_input($_POST['device_token']);

    $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, is_validated = ?, device_token = ? WHERE id = ?");
    $stmt->bind_param("sssisi", $full_name, $email, $phone, $is_validated, $device_token, $student_id);
    if ($stmt->execute()) {
        $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Edited student', 'Student ID: $student_id')");
        if ($is_validated && !$student['is_validated']) {
            $conn->query("INSERT INTO notifications (user_id, user_role, message) VALUES ($student_id, 'student', 'Your account has been validated!')");
        }
        $message = "Student updated successfully.";
        regenerate_csrf_token();
    } else {
        $error = "Failed to update student: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Edit Student</h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form method="POST" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="device_token">Device Token</label>
                <input type="text" id="device_token" name="device_token" value="<?php echo htmlspecialchars($student['device_token']); ?>">
            </div>
            <div class="form-group">
                <label for="is_validated">Validated</label>
                <input type="checkbox" id="is_validated" name="is_validated" <?php echo $student['is_validated'] ? 'checked' : ''; ?>>
            </div>
            <button type="submit" class="btn-action edit">Update Student</button>
            <a href="manage_students.php" class="btn-action delete">Cancel</a>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>