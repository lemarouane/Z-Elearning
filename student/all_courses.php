<?php
require_once '../includes/config.php';
check_login('student');

$student_id = $_SESSION['student_id'];
$courses_query = "
    SELECT DISTINCT c.id, c.title, c.image_path, c.difficulty, s.name AS subject, l.name AS level 
    FROM courses c 
    JOIN subjects s ON c.subject_id = s.id 
    JOIN levels l ON c.level_id = l.id 
    LEFT JOIN user_courses uc ON c.id = uc.course_id AND uc.student_id = ?
    LEFT JOIN student_subjects ss ON c.subject_id = ss.subject_id AND c.level_id = ss.level_id AND ss.student_id = ?
    WHERE uc.student_id = ? 
    OR (ss.student_id = ? AND (ss.difficulty IS NULL OR c.difficulty = ss.difficulty))
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("iiii", $student_id, $student_id, $student_id, $student_id);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/student_header.php'; ?>
    <div class="main-content">
        <main class="dashboard">
            <h1>All Your Courses</h1>
            <section class="course-cards">
                <?php if ($courses->num_rows === 0): ?>
                    <p class="no-data">No courses assigned yet. Contact your admin if this seems incorrect.</p>
                <?php else: ?>
                    <div class="course-grid">
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <div class="course-card">
                                <img src="../uploads/<?php echo htmlspecialchars($course['image_path']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.src='../assets/images/placeholder.jpg';">
                                <div class="course-info">
                                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($course['subject']); ?> - <?php echo htmlspecialchars($course['level']); ?></p>
                                    <span class="difficulty-<?php echo strtolower($course['difficulty']); ?>"><?php echo $course['difficulty']; ?></span>
                                </div>
                                <div class="course-actions">
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                <a href="dashboard.php" class="btn-action back">Back to Dashboard</a>
            </section>
        </main>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html> 