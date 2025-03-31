<?php
require_once '../includes/config.php';
check_login('admin');

$student_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
if (!$student_id) {
    header("Location: manage_students.php");
    exit();
}

$student = $conn->query("SELECT name, email, phone FROM students WHERE id = $student_id")->fetch_assoc();
if (!$student) {
    header("Location: manage_students.php");
    exit();
}

$courses_result = $conn->query("SELECT id, title FROM courses");
$assigned_courses = $conn->query("SELECT course_id FROM student_courses WHERE student_id = $student_id")->fetch_all(MYSQLI_ASSOC);
$assigned_ids = array_column($assigned_courses, 'course_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $new_courses = isset($_POST['courses']) ? array_map('sanitize_input', $_POST['courses']) : [];

    $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone, $student_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("DELETE FROM student_courses WHERE student_id = $student_id");
    foreach ($new_courses as $course_id) {
        $conn->query("INSERT INTO student_courses (student_id, course_id) VALUES ($student_id, $course_id)");
    }

    $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ({$_SESSION['admin_id']}, 'Edited student', 'Student ID: $student_id')");
    header("Location: view_student.php?id=$student_id");
    exit();
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
    <main class="dashboard">
        <h1>Edit Student: <?php echo htmlspecialchars($student['name']); ?></h1>
        <form method="POST" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label>Assigned Courses:</label>
                <?php while ($row = $courses_result->fetch_assoc()): ?>
                    <div>
                        <input type="checkbox" name="courses[]" value="<?php echo $row['id']; ?>" 
                               <?php echo in_array($row['id'], $assigned_ids) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($row['title']); ?>
                    </div>
                <?php endwhile; ?>
            </div>
            <button type="submit" class="btn-action validate">Save Changes</button>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>