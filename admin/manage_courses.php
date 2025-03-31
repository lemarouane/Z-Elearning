<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = '';

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (isset($_POST['delete'])) {
        $course_id = sanitize_input($_POST['course_id']);
        $conn->query("DELETE FROM courses WHERE id = $course_id");
        $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ($admin_id, 'Deleted course', 'Course ID: $course_id')");
        $message = "Course deleted successfully.";
    }
    regenerate_csrf_token();
}

// Fetch all courses
$courses_result = $conn->query("SELECT c.id, c.title, s.name AS subject, l.name AS level, c.content_type, c.created_at 
                                FROM courses c 
                                JOIN subjects s ON c.subject_id = s.id 
                                JOIN levels l ON c.level_id = l.id 
                                ORDER BY c.created_at DESC");
if (!$courses_result) {
    log_error("Courses query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Courses - Zouhair E-learning">
    <title>Manage Courses - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Manage Courses</h1>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <section class="tables">
            <div class="table-container">
                <h2>All Courses</h2>
                <table id="coursesTable" class="display">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Level</th>
                            <th>Content Type</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['level']); ?></td>
                                <td><?php echo htmlspecialchars($row['content_type']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <form method="POST" action="" class="action-form" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this course?');">Delete</button>
                                    </form>
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
        $(document).ready(function() {
            $('#coursesTable').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
</body>
</html>