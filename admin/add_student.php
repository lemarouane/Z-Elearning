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
    $password = $_POST['password'];
    $is_validated = isset($_POST['is_validated']) ? 1 : 0;
    $subject_id = $_POST['subject_id'] ?: NULL;

    $stmt = $conn->prepare("INSERT INTO students (name, email, phone, password, is_validated, subject_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $name, $email, $phone, $password, $is_validated, $subject_id);
    if ($stmt->execute()) {
        $student_id = $conn->insert_id;
        $success = "Student added successfully.";
        $conn->query("INSERT INTO activity_logs (admin_id, action) VALUES ({$_SESSION['admin_id']}, 'Added student: $student_id')");
    } else {
        $error = "Failed to add student. Email may already exist.";
    }
    $stmt->close();
}

$subjects = $conn->query("SELECT id, name FROM subjects");
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
    <div class="sidebar">
        <h2>Zouhair E-learning</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_students.php" class="active">Manage Students</a>
        <a href="manage_courses.php">Manage Courses</a>
        <a href="activity_logs.php">Activity Logs</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="dashboard-container">
        <h2>Add New Student</h2>
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
                <input type="text" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id">
                    <option value="">None</option>
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="is_validated">Validated</label>
                <input type="checkbox" id="is_validated" name="is_validated">
            </div>
            <button type="submit" class="btn">Add Student</button>
            <a href="manage_students.php" class="btn btn-danger">Cancel</a>
        </form>
    </div>
</body>
</html>