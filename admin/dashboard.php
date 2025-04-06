<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];

// Fetch stats
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$validated_students = $conn->query("SELECT COUNT(*) FROM students WHERE is_validated = 1")->fetch_row()[0];
$pending_students = $total_students - $validated_students;
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$total_levels = $conn->query("SELECT COUNT(*) FROM levels")->fetch_row()[0];
$notifications = $conn->query("SELECT COUNT(*) FROM notifications WHERE user_id = $admin_id AND user_role = 'admin' AND is_read = 0")->fetch_row()[0];

// Recent students and courses
$recent_students = $conn->query("SELECT id, full_name, email, is_validated, created_at FROM students ORDER BY created_at DESC LIMIT 5");
$recent_courses = $conn->query("SELECT c.id, c.title, s.name AS subject, l.name AS level, c.difficulty, c.created_at FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN levels l ON c.level_id = l.id ORDER BY c.created_at DESC LIMIT 5");

// Subjects data for chart
$subjects_data = $conn->query("SELECT s.name, COUNT(c.id) as count FROM subjects s LEFT JOIN courses c ON s.id = c.subject_id GROUP BY s.id");
$subjects_chart = [];
while ($row = $subjects_data->fetch_assoc()) {
    $subjects_chart[$row['name']] = $row['count'];
}

// Notifications
$notifs = $conn->query("SELECT id, message, created_at FROM notifications WHERE user_id = $admin_id AND user_role = 'admin' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Admin Dashboard</h1>
        <section class="stats">
            <div class="stat-card"><h3>Total Students</h3><p><?php echo $total_students; ?></p></div>
            <div class="stat-card"><h3>Validated</h3><p><?php echo $validated_students; ?></p></div>
            <div class="stat-card"><h3>Pending</h3><p><?php echo $pending_students; ?></p></div>
            <div class="stat-card"><h3>Total Courses</h3><p><?php echo $total_courses; ?></p></div>
            <div class="stat-card"><h3>Total Levels</h3><p><a href="manage_levels.php"><?php echo $total_levels; ?></a></p></div>
            <div class="stat-card"><h3>New Notifications</h3><p><?php echo $notifications; ?></p></div>
        </section>
        <section class="charts">
            <div class="chart-container"><h2>Student Status</h2><canvas id="studentChart"></canvas></div>
            <div class="chart-container"><h2>Courses by Subject</h2><canvas id="subjectChart"></canvas></div>
        </section>
        <section class="notifications">
            <h2>Recent Notifications</h2>
            <ul>
                <?php while ($notif = $notifs->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($notif['message']); ?> <span>(<?php echo $notif['created_at']; ?>)</span></li>
                <?php endwhile; ?>
            </ul>
        </section>
        <section class="tables">
            <div class="table-container">
                <h2>Recent Students</h2>
                <table id="studentsTable" class="display">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Validated</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($student = $recent_students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo $student['is_validated'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $student['created_at']; ?></td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-container">
                <h2>Recent Courses</h2>
                <table id="coursesTable" class="display">
                    <thead><tr><th>ID</th><th>Title</th><th>Subject</th><th>Level</th><th>Difficulty</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($course = $recent_courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['subject']); ?></td>
                                <td><?php echo htmlspecialchars($course['level']); ?></td>
                                <td class="difficulty-<?php echo strtolower($course['difficulty']); ?>"><?php echo $course['difficulty']; ?></td>
                                <td><?php echo $course['created_at']; ?></td>
                                <td>
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                    <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        new Chart(document.getElementById('studentChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Validated', 'Pending'],
                datasets: [{ label: 'Students', data: [<?php echo $validated_students; ?>, <?php echo $pending_students; ?>], backgroundColor: ['#4CAF50', '#FF9800'] }]
            },
            options: { scales: { y: { beginAtZero: true } }, animation: { duration: 1000, easing: 'easeInOutQuad' } }
        });

        new Chart(document.getElementById('subjectChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: [<?php echo "'" . implode("','", array_keys($subjects_chart)) . "'"; ?>],
                datasets: [{ data: [<?php echo implode(',', array_values($subjects_chart)); ?>], backgroundColor: ['#4CAF50', '#2196F3', '#FF5722'] }]
            },
            options: { plugins: { legend: { position: 'right' } }, animation: { animateRotate: true, duration: 1500 } }
        });

        $(document).ready(function() {
            $('#studentsTable, #coursesTable').DataTable({ pageLength: 5, lengthChange: false, searching: false });
            $('.stat-card').each(function(i) { $(this).delay(i * 200).fadeIn(500); });
        });
    </script>
</body>
</html>