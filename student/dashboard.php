<?php
require_once '../includes/config.php';
check_login('student');

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT full_name, email FROM students WHERE id = $student_id")->fetch_assoc();

// Stats
$total_courses = $conn->query("SELECT COUNT(*) FROM user_courses WHERE student_id = $student_id")->fetch_row()[0];
$completed_courses = $conn->query("SELECT COUNT(*) FROM user_courses WHERE student_id = $student_id AND progress = 100")->fetch_row()[0];
$notifications = $conn->query("SELECT COUNT(*) FROM notifications WHERE user_id = $student_id AND user_role = 'student' AND is_read = 0")->fetch_row()[0];

// Last 3 courses
$courses_query = "
    SELECT DISTINCT c.id, c.title, c.image_path, c.difficulty, s.name AS subject, l.name AS level 
    FROM courses c 
    JOIN subjects s ON c.subject_id = s.id 
    JOIN levels l ON c.level_id = l.id 
    LEFT JOIN user_courses uc ON c.id = uc.course_id AND uc.student_id = $student_id
    LEFT JOIN student_subjects ss ON c.subject_id = ss.subject_id AND c.level_id = ss.level_id AND ss.student_id = $student_id
    WHERE uc.student_id = $student_id 
    OR (ss.student_id = $student_id AND (ss.difficulty IS NULL OR c.difficulty = ss.difficulty))
    ORDER BY c.created_at DESC LIMIT 3
";
$courses = $conn->query($courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/student_header.php'; ?>
    <div class="main-content">
        <main class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?></h1>
            <section class="stats">
                <div class="stat-card"><h3>Total Courses</h3><p><?php echo $total_courses; ?></p></div>
                <div class="stat-card"><h3>Completed</h3><p><?php echo $completed_courses; ?></p></div>
                <div class="stat-card"><h3>Notifications</h3><p><?php echo $notifications; ?></p></div>
            </section>
 
            <section class="course-cards">
                <h2>Your Recent Courses</h2>
                <?php if ($courses->num_rows === 0): ?>
                    <p>No courses assigned yet.</p>
                <?php else: ?>
                    <div class="course-grid">
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <div class="course-card">
                                <img src="../uploads/<?php echo htmlspecialchars($course['image_path']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
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
                    <a href="all_courses.php" class="btn-action view-all">View All Courses</a>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script>
        new Chart(document.getElementById('progressChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                    data: [<?php echo $completed_courses; ?>, <?php echo $total_courses - $completed_courses; ?>],
                    backgroundColor: ['#48bb78', '#2b6cb0']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                animation: { animateScale: true, duration: 1000 }
            }
        });

        document.querySelectorAll('.stat-card').forEach((card, i) => {
            card.style.animation = `fadeInUp 0.5s ease ${i * 0.2}s forwards`;
            card.style.opacity = 0;
        });
    </script>
</body>
</html>