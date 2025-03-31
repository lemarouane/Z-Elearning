<?php
require_once '../includes/config.php';
check_login('admin');

$course_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
if (!$course_id) {
    header("Location: manage_courses.php");
    exit();
}

$course = $conn->query("SELECT title, subject_id, level_id, content_type, content_path, image_path FROM courses WHERE id = $course_id")->fetch_assoc();
if (!$course) {
    header("Location: manage_courses.php");
    exit();
}

$subjects_result = $conn->query("SELECT id, name FROM subjects");
$levels_result = $conn->query("SELECT id, name FROM levels");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = sanitize_input($_POST['title']);
    $subject_id = sanitize_input($_POST['subject_id']);
    $level_id = sanitize_input($_POST['level_id']);
    $content_type = sanitize_input($_POST['content_type']);
    $content_path = $course['content_path'];
    $image_path = $course['image_path'];

    $upload_dir = '../uploads/';
    if ($content_type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $content_file = $_FILES['content_file'];
        $content_path = 'uploads/pdfs/' . basename($content_file['name']);
        move_uploaded_file($content_file['tmp_name'], "../$content_path");
    } elseif ($content_type === 'video') {
        $content_path = sanitize_input($_POST['video_url']);
    }

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $image_file = $_FILES['image_file'];
        $image_path = 'uploads/images/' . basename($image_file['name']);
        move_uploaded_file($image_file['tmp_name'], "../$image_path");
    }

    $stmt = $conn->prepare("UPDATE courses SET title = ?, subject_id = ?, level_id = ?, content_type = ?, content_path = ?, image_path = ? WHERE id = ?");
    $stmt->bind_param("siisssi", $title, $subject_id, $level_id, $content_type, $content_path, $image_path, $course_id);
    if ($stmt->execute()) {
        $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ({$_SESSION['admin_id']}, 'Edited course', 'Course ID: $course_id')");
        header("Location: view_course.php?id=$course_id");
        exit();
    } else {
        $error = "Failed to update course: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Edit Course: <?php echo htmlspecialchars($course['title']); ?></h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="title">Course Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject:</label>
                <select id="subject_id" name="subject_id" required>
                    <?php while ($row = $subjects_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $course['subject_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="level_id">Level:</label>
                <select id="level_id" name="level_id" required>
                    <?php while ($row = $levels_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $course['level_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content_type">Content Type:</label>
                <select id="content_type" name="content_type" onchange="toggleContentInput()" required>
                    <option value="pdf" <?php echo $course['content_type'] === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                    <option value="video" <?php echo $course['content_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
            <div class="form-group" id="content_file_group" style="display:<?php echo $course['content_type'] === 'pdf' ? 'block' : 'none'; ?>;">
                <label for="content_file">Upload New PDF (optional):</label>
                <input type="file" id="content_file" name="content_file" accept=".pdf">
                <p>Current: <a href="../<?php echo htmlspecialchars($course['content_path']); ?>" target="_blank">View PDF</a></p>
            </div>
            <div class="form-group" id="video_url_group" style="display:<?php echo $course['content_type'] === 'video' ? 'block' : 'none'; ?>;">
                <label for="video_url">Video URL:</label>
                <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($course['content_path']); ?>">
            </div>
            <div class="form-group">
                <label for="image_file">Upload New Image (optional):</label>
                <input type="file" id="image_file" name="image_file" accept="image/*">
                <p>Current: <img src="../<?php echo htmlspecialchars($course['image_path']); ?>" alt="Course Image" style="max-width: 100px;"></p>
            </div>
            <button type="submit" class="btn-action validate">Save Changes</button>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        function toggleContentInput() {
            const contentType = document.getElementById('content_type').value;
            document.getElementById('content_file_group').style.display = contentType === 'pdf' ? 'block' : 'none';
            document.getElementById('video_url_group').style.display = contentType === 'video' ? 'block' : 'none';
        }
    </script>
</body>
</html>