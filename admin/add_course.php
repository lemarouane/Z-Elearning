<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';

// Fetch subjects and levels for dropdowns
$subjects_result = $conn->query("SELECT id, name FROM subjects");
$levels_result = $conn->query("SELECT id, name FROM levels");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = sanitize_input($_POST['title']);
    $subject_id = sanitize_input($_POST['subject_id']);
    $level_id = sanitize_input($_POST['level_id']);
    $content_type = sanitize_input($_POST['content_type']);

    if (empty($title) || empty($subject_id) || empty($level_id) || empty($content_type)) {
        $error = "All fields are required.";
    } else {
        // Handle file uploads
        $content_path = '';
        $image_path = '';
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir . 'pdfs')) mkdir($upload_dir . 'pdfs', 0777, true);
        if (!is_dir($upload_dir . 'images')) mkdir($upload_dir . 'images', 0777, true);

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

        if (empty($content_path) || empty($image_path)) {
            $error = "Content file and image are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (title, subject_id, level_id, content_type, content_path, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siisss", $title, $subject_id, $level_id, $content_type, $content_path, $image_path);
            if ($stmt->execute()) {
                $conn->query("INSERT INTO activity_logs (admin_id, action, details) VALUES ($admin_id, 'Created course', 'Title: $title')");
                $message = "Course added successfully.";
                regenerate_csrf_token();
            } else {
                $error = "Failed to add course: " . $conn->error;
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
    <title>Add Course - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Add New Course</h1>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php elseif ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="title">Course Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="subject_id">Subject:</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php while ($row = $subjects_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="level_id">Level:</label>
                <select id="level_id" name="level_id" required>
                    <option value="">-- Select Level --</option>
                    <?php while ($row = $levels_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content_type">Content Type:</label>
                <select id="content_type" name="content_type" onchange="toggleContentInput()" required>
                    <option value="">-- Select Type --</option>
                    <option value="pdf">PDF</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="form-group" id="content_file_group" style="display:none;">
                <label for="content_file">Upload PDF:</label>
                <input type="file" id="content_file" name="content_file" accept=".pdf">
            </div>
            <div class="form-group" id="video_url_group" style="display:none;">
                <label for="video_url">Video URL:</label>
                <input type="url" id="video_url" name="video_url">
            </div>
            <div class="form-group">
                <label for="image_file">Course Image:</label>
                <input type="file" id="image_file" name="image_file" accept="image/*" required>
            </div>
            <button type="submit" class="btn-action validate">Add Course</button>
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