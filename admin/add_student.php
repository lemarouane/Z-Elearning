<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';
$courses = $conn->query("SELECT id, title FROM courses");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $is_validated = isset($_POST['is_validated']) ? 1 : 0;
    $device_token = sanitize_input($_POST['device_token']);
    $course_id = sanitize_input($_POST['course_id']);

    $stmt = $conn->prepare("INSERT INTO students (username, password, full_name, email, phone, is_validated, device_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $username, $password, $full_name, $email, $phone, $is_validated, $device_token);
    if ($stmt->execute()) {
        $student_id = $conn->insert_id;
        if ($is_validated && $course_id) {
            $conn->query("INSERT INTO user_courses (student_id, course_id) VALUES ($student_id, $course_id)");
            $conn->query("INSERT INTO notifications (user_id, user_role, message) VALUES ($student_id, 'student', 'Your account has been validated and enrolled in a course!')");
        }
        $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Added student', 'Student ID: $student_id')");
        $message = "Student added successfully!";
        regenerate_csrf_token();
    } else {
        $error = "Failed to add student: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Add New Student</h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form method="POST" class="course-form">
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
            <div class="form-group">
                <label for="device_token">Device Token (optional)</label>
                <input type="text" id="device_token" name="device_token">
            </div>
            <div class="form-group">
                <label for="is_validated">Validated</label>
                <input type="checkbox" id="is_validated" name="is_validated">
            </div>
            <div class="form-group">
                <label for="course_id">Assign Course (if validated)</label>
                <select id="course_id" name="course_id">
                    <option value="">Select Course</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn-action add">Add Student</button>
            <a href="manage_students.php" class="btn-action delete">Cancel</a>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>