<?php
require_once '../includes/config.php';
check_login('admin');

$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$total_subjects = $conn->query("SELECT COUNT(*) FROM subjects")->fetch_row()[0];
$pending_students = $conn->query("SELECT COUNT(*) FROM students WHERE is_validated = 0")->fetch_row()[0];

$courses = $conn->query("SELECT c.id, c.title, c.image_path, s.name AS subject, l.name AS level 
                         FROM courses c 
                         JOIN subjects s ON c.subject_id = s.id 
                         JOIN levels l ON c.level_id = l.id 
                         LIMIT 6");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Admin Dashboard</h1>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Courses</h3>
                <p><?php echo $total_courses; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Subjects</h3>
                <p><?php echo $total_subjects; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Validations</h3>
                <p><?php echo $pending_students; ?></p>
            </div>
        </div>
        <div class="charts">
            <div class="chart-container">
                <h2>Student Growth</h2>
                <canvas id="studentChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Course Enrollment</h2>
                <canvas id="courseChart"></canvas>
            </div>
        </div>
        <div class="tables">
            <div class="table-container">
                <h2>Recent Courses</h2>
                <div class="course-cards">
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <div class="course-card" data-id="<?php echo $course['id']; ?>">
                            <img src="../<?php echo htmlspecialchars($course['image_path']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <div class="course-info">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p><?php echo htmlspecialchars($course['subject']); ?> - <?php echo htmlspecialchars($course['level']); ?></p>
                            </div>
                            <div class="course-actions">
                                <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/admin.js"></script>
    <script>
        const studentChart = new Chart(document.getElementById('studentChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Students',
                    data: [10, 20, 15, 25, 30, 35],
                    borderColor: '#2b6cb0',
                    fill: false
                }]
            }
        });

        const courseChart = new Chart(document.getElementById('courseChart'), {
            type: 'bar',
            data: {
                labels: ['Course 1', 'Course 2', 'Course 3', 'Course 4'],
                datasets: [{
                    label: 'Enrollments',
                    data: [50, 30, 20, 40],
                    backgroundColor: '#2b6cb0'
                }]
            }
        });
    </script>
</body>
</html>