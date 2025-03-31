<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT id, name, email, phone, is_validated FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: manage_students.php");
    exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $is_validated = isset($_POST['is_validated']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, is_validated = ? WHERE id = ?");
    $stmt->bind_param("sssii", $name, $email, $phone, $is_validated, $student_id);
    if ($stmt->execute()) {
        $success = "Student updated successfully.";
        $conn->query("INSERT INTO activity_logs (admin_id, action) VALUES ({$_SESSION['admin_id']}, 'Edited student: $student_id')");
    } else {
        $error = "Failed to update student.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="dashboard-container">
        <h2>Edit Student</h2>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" style="max-width: 500px;">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo $student['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo $student['phone']; ?>" required>
            </div>
            <div class="form-group">
                <label for="is_validated">Validated</label>
                <input type="checkbox" id="is_validated" name="is_validated" <?php echo $student['is_validated'] ? 'checked' : ''; ?>>
            </div>
            <button type="submit" class="btn">Update Student</button>
            <a href="manage_students.php" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>