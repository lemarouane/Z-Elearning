<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT id, name, email, phone, is_validated, created_at FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: manage_students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="dashboard-container">
        <h2>Student Details</h2>
        <div class="student-details">
            <p><strong>ID:</strong> <?php echo $student['id']; ?></p>
            <p><strong>Name:</strong> <?php echo $student['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $student['phone']; ?></p>
            <p><strong>Validated:</strong> <?php echo $student['is_validated'] ? 'Yes' : 'No'; ?></p>
            <p><strong>Registered:</strong> <?php echo $student['created_at']; ?></p>
        </div>
        <a href="manage_students.php" class="btn" style="margin-top: 20px;">Back to Students</a>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>