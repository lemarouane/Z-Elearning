<?php
require_once '../includes/config.php';
check_login('admin');

$courses = $conn->query("SELECT c.id, c.title, c.image_path, c.difficulty, s.name AS subject, l.name AS level FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN levels l ON c.level_id = l.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <div class="manage-courses-container">
            <h1>Manage Courses</h1>
            <a href="add_course.php" class="btn-action add"><i class="fas fa-plus"></i> Add Course</a>
            <input type="text" id="courseSearch" placeholder="Search courses..." class="search-bar">
            <div class="course-cards">
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <div class="course-card" data-id="<?php echo $course['id']; ?>">
                        <img src="../uploads/<?php echo htmlspecialchars($course['image_path']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars($course['subject']); ?> - <?php echo htmlspecialchars($course['level']); ?></p>
                            <span class="difficulty-<?php echo strtolower($course['difficulty']); ?>"><?php echo $course['difficulty']; ?></span>
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
    <script>
        $(document).ready(function() {
            $('#courseSearch').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('.course-card').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>