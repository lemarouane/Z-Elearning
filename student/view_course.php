<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT c.id, c.title, s.name AS subject, l.name AS level, c.image_path 
                        FROM courses c 
                        JOIN subjects s ON c.subject_id = s.id 
                        JOIN levels l ON c.level_id = l.id 
                        JOIN enrollments e ON c.id = e.course_id 
                        WHERE e.student_id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

if ($courses->num_rows == 0) {
    $message = "You are not enrolled in any courses yet.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="sidebar">
        <h2>Zouhair E-learning</h2>
        <a href="view_course.php" class="active">My Courses</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="dashboard-container">
        <h2>My Courses</h2>
        <?php if (isset($message)): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php else: ?>
            <div class="course-grid">
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <img src="../uploads/images/<?php echo $row['image_path']; ?>" alt="<?php echo $row['title']; ?>">
                        <h3><?php echo $row['title']; ?></h3>
                        <p>Subject: <?php echo $row['subject']; ?></p>
                        <p>Level: <?php echo $row['level']; ?></p>
                        <a href="course_detail.php?id=<?php echo $row['id']; ?>" class="btn">View Course</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>