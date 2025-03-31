<?php
require_once '../includes/config.php';
check_login('admin');

$student_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
if (!$student_id) {
    header("Location: manage_students.php");
    exit();
}

$student = $conn->query("SELECT name, email, phone, is_validated, created_at FROM students WHERE id = $student_id")->fetch_assoc();
if (!$student) {
    header("Location: manage_students.php");
    exit();
}

$courses_result = $conn->query("SELECT c.id, c.title, l.name AS level 
                                FROM student_courses sc 
                                JOIN courses c ON sc.course_id = c.id 
                                JOIN levels l ON c.level_id = l.id 
                                WHERE sc.student_id = $student_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>View Student: <?php echo htmlspecialchars($student['name']); ?></h1>
        <section class="details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
            <p><strong>Validated:</strong> <?php echo $student['is_validated'] ? 'Yes' : 'No'; ?></p>
            <p><strong>Registered:</strong> <?php echo $student['created_at']; ?></p>
        </section>
        <section class="tables">
            <div class="table-container">
                <h2>Assigned Courses</h2>
                <table id="coursesTable" class="display">
                    <thead>
                        <tr><th>ID</th><th>Title</th><th>Level</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['level']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <a href="manage_students.php" class="btn-action">Back to Students</a>
        <a href="edit_student.php?id=<?php echo $student_id; ?>" class="btn-action edit"><i class="fas fa-edit"></i> Edit</a>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#coursesTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
</body>
</html>