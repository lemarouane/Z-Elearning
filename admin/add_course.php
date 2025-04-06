<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';

// Fetch subjects and levels for dropdowns
$subjects = $conn->query("SELECT id, name FROM subjects");
$levels = $conn->query("SELECT id, name FROM levels");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = sanitize_input($_POST['title']);
    $subject_id = sanitize_input($_POST['subject_id']);
    $level_id = sanitize_input($_POST['level_id']);
    $content_type = sanitize_input($_POST['content_type']);
    $difficulty = sanitize_input($_POST['difficulty']);

    $upload_dir = '../uploads/';
    $content_path = $image_path = '';

    // Validate required fields
    if (empty($title) || !$subject_id || !$level_id || !$content_type || !$difficulty) {
        $error = "All fields are required.";
    } else {
        // Handle PDF upload
        if ($content_type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $pdf = $_FILES['pdf_file'];
            if ($pdf['type'] === 'application/pdf') {
                $content_path = "pdfs/" . time() . "_" . basename($pdf['name']);
                if (!move_uploaded_file($pdf['tmp_name'], $upload_dir . $content_path)) {
                    $error = "Failed to upload PDF.";
                    log_error("PDF upload failed: " . $content_path);
                }
            } else {
                $error = "Only PDF files are allowed.";
            }
        } elseif ($content_type === 'video') {
            $content_path = sanitize_input($_POST['video_url']);
            if (!preg_match('/^https:\/\/www\.youtube\.com\/embed\/[a-zA-Z0-9_-]+$/', $content_path)) {
                $error = "Invalid YouTube embed URL.";
            }
        }

        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image_file'];
            if (in_array($image['type'], ['image/jpeg', 'image/png'])) {
                $image_path = "images/" . time() . "_" . basename($image['name']);
                if (!move_uploaded_file($image['tmp_name'], $upload_dir . $image_path)) {
                    $error = "Failed to upload image.";
                    log_error("Image upload failed: " . $image_path);
                }
            } else {
                $error = "Only JPEG/PNG images are allowed.";
            }
        } else {
            $error = "Course image is required.";
        }

        // Proceed if no errors and files are uploaded
        if (!$error && $content_path && $image_path) {
            // Insert the course
            $stmt = $conn->prepare("INSERT INTO courses (title, subject_id, level_id, content_type, content_path, image_path, difficulty, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissssi", $title, $subject_id, $level_id, $content_type, $content_path, $image_path, $difficulty, $admin_id);
            if ($stmt->execute()) {
                $course_id = $conn->insert_id;

                // Log the action
                $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Added course', 'Course ID: $course_id')");

                // Assign course to students with subject-level access
                $assign_stmt = $conn->prepare("SELECT student_id FROM student_subjects WHERE subject_id = ? AND level_id = ? AND (difficulty IS NULL OR difficulty = ?)");
                $assign_stmt->bind_param("iis", $subject_id, $level_id, $difficulty);
                $assign_stmt->execute();
                $students = $assign_stmt->get_result();
                while ($student = $students->fetch_assoc()) {
                    $student_id = $student['student_id'];
                    $conn->query("INSERT IGNORE INTO user_courses (student_id, course_id) VALUES ($student_id, $course_id)");
                    $conn->query("INSERT INTO notifications (user_id, user_role, message) 
                                  VALUES ($student_id, 'student', 'A new course \"$title\" has been added to your subject!')");
                }
                $assign_stmt->close();

                $message = "Course added successfully!";
                regenerate_csrf_token();
            } else {
                $error = "Failed to add course: " . $conn->error;
            }
            $stmt->close(); // Close only once after all operations
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Add New Course</h1>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="level_id">Level</label>
                <select id="level_id" name="level_id" required>
                    <option value="">Select Level</option>
                    <?php while ($row = $levels->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="">Select Difficulty</option>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
            <div class="form-group">
                <label for="content_type">Content Type</label>
                <select id="content_type" name="content_type" onchange="toggleContent()" required>
                    <option value="">Select Type</option>
                    <option value="pdf">PDF</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="form-group" id="pdf_group" style="display:none;">
                <label for="pdf_file">Upload PDF</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf">
            </div>
            <div class="form-group" id="video_group" style="display:none;">
                <label for="video_url">YouTube Embed URL</label>
                <input type="url" id="video_url" name="video_url" placeholder="https://www.youtube.com/embed/...">
            </div>
            <div class="form-group">
                <label for="image_file">Course Image</label>
                <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png" required>
            </div>
            <button type="submit" class="btn-action add">Add Course</button>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        function toggleContent() {
            const type = document.getElementById('content_type').value;
            document.getElementById('pdf_group').style.display = type === 'pdf' ? 'block' : 'none';
            document.getElementById('video_group').style.display = type === 'video' ? 'block' : 'none';
            if (type !== 'pdf') document.getElementById('pdf_file').value = '';
            if (type !== 'video') document.getElementById('video_url').value = '';
        }
    </script>
</body>
</html>