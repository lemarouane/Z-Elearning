<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';

// Fetch ALL students (not just pending)
$students = $conn->query("SELECT id, username, full_name, email, is_validated FROM students ORDER BY is_validated ASC, id DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (isset($_POST['validate'])) {
        $student_id = sanitize_input($_POST['student_id']);
        $level_id = sanitize_input($_POST['level_id']);
        $subject_id = sanitize_input($_POST['subject_id']);
        $difficulty = sanitize_input($_POST['difficulty']);
        $course_selection = $_POST['course_selection'] ?? [];

        // Validate the student
        $stmt = $conn->prepare("UPDATE students SET is_validated = 1 WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            $error = "Failed to validate student: " . $conn->error;
        }
        $stmt->close();

        if (!$error) {
            if (in_array('all', $course_selection)) {
                // Store subject-level access
                $difficulty_value = empty($difficulty) ? NULL : $difficulty;
                $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id, level_id, difficulty) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $student_id, $subject_id, $level_id, $difficulty_value);
                if (!$stmt->execute()) {
                    $error = "Failed to assign subject access: " . $conn->error;
                }
                $stmt->close();

                // Assign existing courses
                $query = "SELECT id FROM courses WHERE subject_id = ? AND level_id = ?";
                if ($difficulty) $query .= " AND difficulty = ?";
                $stmt = $conn->prepare($query);
                if ($difficulty) {
                    $stmt->bind_param("iis", $subject_id, $level_id, $difficulty);
                } else {
                    $stmt->bind_param("ii", $subject_id, $level_id);
                }
                $stmt->execute();
                $courses = $stmt->get_result();
                while ($course = $courses->fetch_assoc()) {
                    $course_id = $course['id'];
                    $conn->query("INSERT IGNORE INTO user_courses (student_id, course_id) VALUES ($student_id, $course_id)");
                }
                $stmt->close();
            } else {
                // Assign specific courses
                foreach ($course_selection as $course_id) {
                    $course_id = sanitize_input($course_id);
                    $conn->query("INSERT IGNORE INTO user_courses (student_id, course_id) VALUES ($student_id, $course_id)");
                }
            }

            if (!$error) {
                $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Validated student', 'Student ID: $student_id')");
                $conn->query("INSERT INTO notifications (user_id, user_role, message) VALUES ($student_id, 'student', 'Your account has been validated and courses assigned!')");
                $message = "Student validated and courses assigned successfully.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Manage Students</h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo $student['is_validated'] ? '<span class="status-validated">Validated</span>' : '<span class="status-pending">Pending</span>'; ?></td>
                        <td>
                            <?php if (!$student['is_validated']): ?>
                                <button class="btn-action validate" onclick="openValidateModal(<?php echo $student['id']; ?>)">Validate</button>
                            <?php else: ?>
                                <span class="validated-text">Validated</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="validateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeValidateModal()">×</span>
                <h2>Validate & Assign Courses</h2>
                <form method="POST" id="validateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="student_id" id="modalStudentId">
                    <div class="form-group">
                        <label for="level_id">Study Level</label>
                        <select name="level_id" id="level_id" onchange="updateSubjects()" required>
                            <option value="">Select Level</option>
                            <?php $levels = $conn->query("SELECT id, name FROM levels"); while ($level = $levels->fetch_assoc()): ?>
                                <option value="<?php echo $level['id']; ?>"><?php echo htmlspecialchars($level['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject_id">Subject</label>
                        <select name="subject_id" id="subject_id" onchange="updateCourses()" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Difficulty</label>
                        <select name="difficulty" id="difficulty" onchange="updateCourses()">
                            <option value="">All Difficulties</option>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course_selection">Course Selection</label>
                        <select name="course_selection[]" id="course_selection" multiple size="5">
                            <option value="all">All Courses in Subject</option>
                        </select>
                    </div>
                    <button type="submit" name="validate" class="btn-action validate">Validate</button>
                </form>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        function updateSubjects() {
            const levelId = $('#level_id').val();
            if (levelId) {
                $.ajax({
                    url: 'fetch_subjects.php',
                    type: 'POST',
                    data: { level_id: levelId },
                    success: function(data) {
                        $('#subject_id').html(data);
                        updateCourses();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching subjects:', error);
                        $('#subject_id').html('<option value="">Error loading subjects</option>');
                    }
                });
            } else {
                $('#subject_id').html('<option value="">Select a Level First</option>');
            }
        }

        function updateCourses() {
            const subjectId = $('#subject_id').val();
            const difficulty = $('#difficulty').val();
            if (subjectId) {
                $.ajax({
                    url: 'fetch_courses.php',
                    type: 'POST',
                    data: { subject_id: subjectId, difficulty: difficulty },
                    success: function(data) {
                        $('#course_selection').html('<option value="all">All Courses in Subject</option>' + data);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching courses:', error);
                        $('#course_selection').html('<option value="all">All Courses in Subject</option><option value="">Error loading courses</option>');
                    }
                });
            }
        }

        function openValidateModal(id) {
            $('#modalStudentId').val(id);
            $('#validateModal').fadeIn(200);
            updateSubjects();
        }

        function closeValidateModal() {
            $('#validateModal').fadeOut(200);
        }
    </script>
</body>
</html>