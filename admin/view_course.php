<?php
require_once '../includes/config.php';
check_login('admin');

$course_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
if (!$course_id) {
    header("Location: manage_courses.php");
    exit();
}

$course = $conn->query("SELECT c.title, c.content_type, c.content_path, c.image_path, c.created_at, s.name AS subject, l.name AS level 
                        FROM courses c 
                        JOIN subjects s ON c.subject_id = s.id 
                        JOIN levels l ON c.level_id = l.id 
                        WHERE c.id = $course_id")->fetch_assoc();
if (!$course) {
    header("Location: manage_courses.php");
    exit();
}

// Convert YouTube URL to embed format if applicable
$embed_url = $course['content_type'] === 'video' ? preg_replace(
    "/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/", 
    "https://www.youtube.com/embed/$1?controls=0&rel=0&modestbranding=1", 
    $course['content_path']
) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <div class="course-view-container">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <div class="course-meta">
                <span class="meta-item"><i class="fas fa-book"></i> <?php echo htmlspecialchars($course['subject']); ?></span>
                <span class="meta-item"><i class="fas fa-signal"></i> <?php echo htmlspecialchars($course['level']); ?></span>
                <span class="meta-item"><i class="fas fa-clock"></i> <?php echo $course['created_at']; ?></span>
            </div>
            <div class="course-content">
                <div class="content-preview">
                    <?php if ($course['content_type'] === 'pdf'): ?>
                        <iframe src="stream_pdf.php?file=<?php echo urlencode($course['content_path']); ?>" class="embedded-pdf" frameborder="0"></iframe>
                    <?php elseif ($course['content_type'] === 'video'): ?>
                        <div class="video-wrapper">
                            <iframe src="<?php echo htmlspecialchars($embed_url); ?>" class="embedded-video" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="course-sidebar">
                    <div class="course-image">
                        <img src="../<?php echo htmlspecialchars($course['image_path']); ?>" alt="Course Image">
                    </div>
                    <div class="course-actions">
                        <a href="manage_courses.php" class="btn-action"><i class="fas fa-arrow-left"></i> Back to Courses</a>
                        <a href="edit_course.php?id=<?php echo $course_id; ?>" class="btn-action edit"><i class="fas fa-edit"></i> Edit Course</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>