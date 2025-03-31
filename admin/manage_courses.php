<?php
require_once '../includes/config.php';
check_login('admin');

$courses = $conn->query("SELECT c.id, c.title, c.image_path, s.name AS subject, l.name AS level 
                         FROM courses c 
                         JOIN subjects s ON c.subject_id = s.id 
                         JOIN levels l ON c.level_id = l.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <div class="manage-courses-container">
            <h1>Manage Courses</h1>
            <a href="add_course.php" class="btn-action add"><i class="fas fa-plus"></i> Add New Course</a>
            <div class="course-cards">
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <div class="course-card" data-id="<?php echo $course['id']; ?>">
                        <img src="../<?php echo htmlspecialchars($course['image_path']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars($course['subject']); ?> - <?php echo htmlspecialchars($course['level']); ?></p>
                        </div>
                        <div class="course-actions">
                            <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                            <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                            <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>