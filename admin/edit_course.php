<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$course_id = $_GET['id'] ?? 0;
$message = $error = '';

$stmt = $conn->prepare("SELECT title, subject_id, level_id, content_type, content_path, image_path, difficulty FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: manage_courses.php");
    exit();
}

$subjects = $conn->query("SELECT id, name FROM subjects");
$levels = $conn->query("SELECT id, name FROM levels");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = sanitize_input($_POST['title']);
    $subject_id = sanitize_input($_POST['subject_id']);
    $level_id = sanitize_input($_POST['level_id']);
    $content_type = sanitize_input($_POST['content_type']);
    $difficulty = sanitize_input($_POST['difficulty']);
    $content_path = $course['content_path'];
    $image_path = $course['image_path'];

    $upload_dir = '../uploads/';

    if (empty($title) || !$subject_id || !$level_id || !$content_type || !$difficulty) {
        $error = "All fields are required.";
    } else {
        if ($content_type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $pdf = $_FILES['pdf_file'];
            if ($pdf['type'] === 'application/pdf') {
                $content_path = "pdfs/" . time() . "_" . basename($pdf['name']);
                move_uploaded_file($pdf['tmp_name'], $upload_dir . $content_path);
            } else {
                $error = "Only PDF files are allowed.";
            }
        } elseif ($content_type === 'video') {
            $content_path = sanitize_input($_POST['video_url']);
            if (!preg_match('/^https:\/\/www\.youtube\.com\/embed\/[a-zA-Z0-9_-]+$/', $content_path)) {
                $error = "Invalid YouTube embed URL.";
            }
        }

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image_file'];
            if (in_array($image['type'], ['image/jpeg', 'image/png'])) {
                $image_path = "images/" . time() . "_" . basename($image['name']);
                move_uploaded_file($image['tmp_name'], $upload_dir . $image_path);
            } else {
                $error = "Only JPEG/PNG images are allowed.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("UPDATE courses SET title = ?, subject_id = ?, level_id = ?, content_type = ?, content_path = ?, image_path = ?, difficulty = ? WHERE id = ?");
            $stmt->bind_param("siissssi", $title, $subject_id, $level_id, $content_type, $content_path, $image_path, $difficulty, $course_id);
            if ($stmt->execute()) {
                $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Edited course', 'Course ID: $course_id')");
                $message = "Course updated successfully!";
                regenerate_csrf_token();
            } else {
                $error = "Failed to update course: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Edit Course</h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $course['subject_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="level_id">Level</label>
                <select id="level_id" name="level_id" required>
                    <?php while ($row = $levels->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $course['level_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <?php foreach (['Easy', 'Medium', 'Hard'] as $diff): ?>
                        <option value="<?php echo $diff; ?>" <?php echo $course['difficulty'] === $diff ? 'selected' : ''; ?>><?php echo $diff; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content_type">Content Type</label>
                <select id="content_type" name="content_type" onchange="toggleContent()" required>
                    <option value="pdf" <?php echo $course['content_type'] === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                    <option value="video" <?php echo $course['content_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
            <div class="form-group" id="pdf_group" style="display:<?php echo $course['content_type'] === 'pdf' ? 'block' : 'none'; ?>;">
                <label for="pdf_file">Upload New PDF (optional)</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf">
                <p>Current: <?php echo htmlspecialchars(basename($course['content_path'])); ?></p>
            </div>
            <div class="form-group" id="video_group" style="display:<?php echo $course['content_type'] === 'video' ? 'block' : 'none'; ?>;">
                <label for="video_url">YouTube Embed URL</label>
                <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($course['content_type'] === 'video' ? $course['content_path'] : ''); ?>" placeholder="https://www.youtube.com/embed/...">
            </div>
            <div class="form-group">
                <label for="image_file">Course Image (optional)</label>
                <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png">
                <p>Current: <?php echo htmlspecialchars(basename($course['image_path'])); ?></p>
            </div>
            <button type="submit" class="btn-action edit">Update Course</button>
            <a href="manage_courses.php" class="btn-action delete">Cancel</a>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        function toggleContent() {
            const type = document.getElementById('content_type').value;
            document.getElementById('pdf_group').style.display = type === 'pdf' ? 'block' : 'none';
            document.getElementById('video_group').style.display = type === 'video' ? 'block' : 'none';
        }
    </script>
</body>
</html>