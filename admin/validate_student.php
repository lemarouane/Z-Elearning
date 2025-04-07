<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$student_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
$message = $error = '';

if (!$student_id || !$conn->query("SELECT id FROM students WHERE id = $student_id AND is_validated = 0")->num_rows) {
    header("Location: dashboard.php");
    exit();
}

$student = $conn->query("SELECT full_name, email FROM students WHERE id = $student_id")->fetch_assoc();
$subjects = $conn->query("SELECT id, name FROM subjects");
$courses = $conn->query("SELECT c.id, c.title, s.name AS subject, l.name AS level FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN levels l ON c.level_id = l.id");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $subject_id = sanitize_input($_POST['subject_id']);
    $difficulty = sanitize_input($_POST['difficulty']);
    $selected_courses = isset($_POST['courses']) ? array_map('sanitize_input', $_POST['courses']) : [];

    if (!$subject_id) {
        $error = "Please select a subject.";
    } else {
        $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id, difficulty) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $student_id, $subject_id, $difficulty);
        if ($stmt->execute()) {
            if (!empty($selected_courses)) {
                $placeholders = implode(',', array_fill(0, count($selected_courses), '(?, ?)'));
                $stmt = $conn->prepare("INSERT INTO user_courses (student_id, course_id) VALUES $placeholders");
                $params = [];
                foreach ($selected_courses as $course_id) {
                    $params[] = $student_id;
                    $params[] = $course_id;
                }
                $stmt->bind_param(str_repeat('ii', count($selected_courses)), ...$params);
                $stmt->execute();
            }
            $conn->query("UPDATE students SET is_validated = 1 WHERE id = $student_id");
            $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Validated student', 'Student ID: $student_id')");
            $message = "Student validated successfully!";
            regenerate_csrf_token();
            header("Refresh: 2; url=dashboard.php");
        } else {
            $error = "Validation failed: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Student - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Validate Student: <?php echo htmlspecialchars($student['full_name']); ?></h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form method="POST" class="validation-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="subject_id">Assign Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="difficulty">Difficulty Level</label>
                <select id="difficulty" name="difficulty">
                    <option value="">All Difficulties</option>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
            <div class="form-group">
                <label>Specific Courses (optional)</label>
                <div id="courseList" class="course-checkboxes">
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <div class="course-option">
                            <input type="checkbox" name="courses[]" value="<?php echo $course['id']; ?>" data-subject="<?php echo htmlspecialchars($course['subject']); ?>">
                            <label><?php echo htmlspecialchars($course['title'] . " (" . $course['subject'] . " - " . $course['level'] . ")"); ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <button type="submit" class="btn-action validate">Validate Student</button>
            <a href="dashboard.php" class="btn-action cancel">Cancel</a>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#subject_id').change(function() {
                const subject = $(this).find('option:selected').text();
                $('.course-option').each(function() {
                    $(this).toggle($(this).find('input').data('subject') === subject || !subject);
                });
            });
        });
    </script>
</body>
</html>