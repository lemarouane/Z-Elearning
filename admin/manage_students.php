<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = '';

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (isset($_POST['validate'])) {
        $student_id = sanitize_input($_POST['student_id']);
        $course_id = sanitize_input($_POST['course_id']);
        $level_id = sanitize_input($_POST['level_id']);
        $conn->query("UPDATE students SET is_validated = 1 WHERE id = $student_id");
        $conn->query("INSERT INTO student_courses (student_id, course_id) VALUES ($student_id, $course_id)");
        $conn->query("UPDATE courses SET level_id = $level_id WHERE id = $course_id");
        $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ($admin_id, 'Validated student', 'Student ID: $student_id assigned to Course ID: $course_id, Level ID: $level_id')");
        $message = "Student validated and assigned successfully.";
    } elseif (isset($_POST['delete'])) {
        $student_id = sanitize_input($_POST['student_id']);
        $conn->query("DELETE FROM students WHERE id = $student_id");
        $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ($admin_id, 'Deleted student', 'Student ID: $student_id')");
        $message = "Student deleted successfully.";
    }
    regenerate_csrf_token();
}

// Fetch all students, courses, and levels
$students_result = $conn->query("SELECT id, name, email, phone, is_validated, created_at FROM students ORDER BY created_at DESC");
$courses_result = $conn->query("SELECT id, title FROM courses");
$levels_result = $conn->query("SELECT id, name FROM levels");
$courses = [];
$levels = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}
while ($row = $levels_result->fetch_assoc()) {
    $levels[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Manage Students</h1>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <section class="tables">
            <div class="table-container">
                <h2>All Students</h2>
                <table id="studentsTable" class="display">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Validated</th><th>Registered</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo $row['is_validated'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $row['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                    <?php if (!$row['is_validated']): ?>
                                        <button class="btn-action validate" onclick="openValidateModal(<?php echo $row['id']; ?>)"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
                                    <form method="POST" action="" class="action-form" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn-action delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Validation Modal -->
        <div id="validateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeValidateModal()">×</span>
                <h2>Assign Course and Level</h2>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="student_id" id="modalStudentId">
                    <div class="form-group">
                        <label for="course_id">Select Course:</label>
                        <select name="course_id" id="course_id" required>
                            <option value="">-- Select a Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="level_id">Select Level:</label>
                        <select name="level_id" id="level_id" required>
                            <option value="">-- Select a Level --</option>
                            <?php foreach ($levels as $level): ?>
                                <option value="<?php echo $level['id']; ?>"><?php echo htmlspecialchars($level['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="validate" class="btn-action validate">Validate & Assign</button>
                </form>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true
            });
        });

        function openValidateModal(studentId) {
            document.getElementById('modalStudentId').value = studentId;
            document.getElementById('validateModal').style.display = 'block';
        }

        function closeValidateModal() {
            document.getElementById('validateModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('validateModal')) {
                closeValidateModal();
            }
        }
    </script>
</body>
</html>