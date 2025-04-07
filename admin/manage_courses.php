<?php
require_once '../includes/config.php';
check_login('admin');

$courses = $conn->query("SELECT c.id, c.title, c.content_type, c.content_path, c.difficulty, s.name AS subject, l.name AS level 
                         FROM courses c 
                         JOIN subjects s ON c.subject_id = s.id 
                         JOIN levels l ON c.level_id = l.id 
                         ORDER BY c.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="main-content">
        <div class="dashboard">
            <h1>Manage Courses</h1>
            <a href="add_course.php" class="btn-action add"><i class="fas fa-plus"></i> Add New Course</a>
            <section class="tables">
                <div class="table-container">
                    <h2>All Courses</h2>
                    <table id="coursesTable" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Level</th>
                                <th>Difficulty</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $courses->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $course['id']; ?></td>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><?php echo htmlspecialchars($course['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($course['level']); ?></td>
                                    <td class="difficulty-<?php echo strtolower($course['difficulty']); ?>">
                                        <?php echo $course['difficulty']; ?>
                                    </td>
                                    <td>
                                        <?php if ($course['content_type'] === 'pdf'): ?>
                                            <a href="stream_pdf.php?file=<?php echo urlencode($course['content_path']); ?>" target="_blank">View PDF</a>
                                        <?php elseif ($course['content_type'] === 'video'): ?>
                                            <a href="<?php echo htmlspecialchars($course['content_path']); ?>" target="_blank">View Video</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#coursesTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                language: { search: "Search courses:" }
            });
        });
    </script>
</body>
</html>