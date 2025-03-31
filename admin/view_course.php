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
        <h1>View Course: <?php echo htmlspecialchars($course['title']); ?></h1>
        <section class="details">
            <p><strong>Subject:</strong> <?php echo htmlspecialchars($course['subject']); ?></p>
            <p><strong>Level:</strong> <?php echo htmlspecialchars($course['level']); ?></p>
            <p><strong>Content Type:</strong> <?php echo htmlspecialchars($course['content_type']); ?></p>
            <p><strong>Content:</strong> 
                <?php if ($course['content_type'] === 'pdf'): ?>
                    <a href="../<?php echo htmlspecialchars($course['content_path']); ?>" target="_blank">View PDF</a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($course['content_path']); ?>" target="_blank">Watch Video</a>
                <?php endif; ?>
            </p>
            <p><strong>Image:</strong> <img src="../<?php echo htmlspecialchars($course['image_path']); ?>" alt="Course Image" style="max-width: 200px;"></p>
            <p><strong>Created:</strong> <?php echo $course['created_at']; ?></p>
            <a href="manage_courses.php" class="btn-action">Back to Courses</a>
            <a href="edit_course.php?id=<?php echo $course_id; ?>" class="btn-action edit"><i class="fas fa-edit"></i> Edit</a>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>