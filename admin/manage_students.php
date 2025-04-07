<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';

$students = $conn->query("SELECT id, full_name, email, is_validated, created_at FROM students ORDER BY created_at DESC");
$subjects = $conn->query("SELECT id, name FROM subjects");
$courses = $conn->query("SELECT c.id, c.title, s.name AS subject, l.name AS level FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN levels l ON c.level_id = l.id");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $student_id = sanitize_input($_POST['student_id']);
    $subjects_data = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    if (empty($subjects_data)) {
        $error = "Please assign at least one subject.";
    } else {
        $conn->begin_transaction();
        try {
            foreach ($subjects_data as $subject_id => $data) {
                $difficulty = sanitize_input($data['difficulty']);
                $all_courses = isset($data['all_courses']) && $data['all_courses'] === 'on';
                $selected_courses = isset($data['courses']) ? array_map('sanitize_input', $data['courses']) : [];

                $stmt = $conn->prepare("INSERT IGNORE INTO student_subjects (student_id, subject_id, difficulty, auto_assign_new_courses) VALUES (?, ?, ?, ?)");
                $auto_assign = $all_courses ? 1 : 0;
                $stmt->bind_param("iisi", $student_id, $subject_id, $difficulty, $auto_assign);
                $stmt->execute();

                if (!$all_courses && !empty($selected_courses)) {
                    $placeholders = implode(',', array_fill(0, count($selected_courses), '(?, ?)'));
                    $stmt = $conn->prepare("INSERT IGNORE INTO user_courses (student_id, course_id) VALUES $placeholders");
                    $params = [];
                    foreach ($selected_courses as $course_id) {
                        $params[] = $student_id;
                        $params[] = $course_id;
                    }
                    $stmt->bind_param(str_repeat('ii', count($selected_courses)), ...$params);
                    $stmt->execute();
                }
            }
            $conn->query("UPDATE students SET is_validated = 1 WHERE id = $student_id");
            $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Validated student', 'Student ID: $student_id')");
            $conn->commit();
            $message = "Student validated successfully!";
            regenerate_csrf_token();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Validation failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="main-content">
        <div class="dashboard">
            <h1>Manage Students</h1>
            <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <section class="tables">
                <div class="table-container">
                    <h2>All Students</h2>
                    <table id="studentsTable" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Validated</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo $student['is_validated'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $student['created_at']; ?></td>
                                    <td>
                                        <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                        <?php if (!$student['is_validated']): ?>
                                            <button class="btn-action validate" data-student-id="<?php echo $student['id']; ?>" data-student-name="<?php echo htmlspecialchars($student['full_name']); ?>"><i class="fas fa-check"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <div id="validationModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Validate <span id="studentName"></span></h2>
                    <form method="POST" class="validation-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="student_id" id="studentId">
                        <div id="subjectsContainer">
                            <div class="subject-group">
                                <div class="form-group">
                                    <label>Subject</label>
                                    <select name="subjects[0][subject_id]" class="subject-select" required>
                                        <option value="">Select Subject</option>
                                        <?php $subjects->data_seek(0); while ($subject = $subjects->fetch_assoc()): ?>
                                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Difficulty</label>
                                    <select name="subjects[0][difficulty]">
                                        <option value="">All Difficulties</option>
                                        <option value="Easy">Easy</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Hard">Hard</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="subjects[0][all_courses]" class="all-courses"> Auto-assign all new courses</label>
                                </div>
                                <div class="form-group course-selection" style="display: none;">
                                    <label>Specific Courses (optional)</label>
                                    <div class="course-checkboxes">
                                        <?php $courses->data_seek(0); while ($course = $courses->fetch_assoc()): ?>
                                            <div class="course-option" data-subject="<?php echo htmlspecialchars($course['subject']); ?>">
                                                <input type="checkbox" name="subjects[0][courses][]" value="<?php echo $course['id']; ?>">
                                                <label><?php echo htmlspecialchars($course['title'] . " (" . $course['subject'] . " - " . $course['level'] . ")"); ?></label>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-action remove-subject" style="display: none;">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn-action add-subject">Add Subject</button>
                        <button type="submit" class="btn-action validate">Validate Student</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                language: { search: "Search students:" }
            });

            const modal = $('#validationModal');
            const span = $('.close');
            let subjectCount = 1;

            $('.validate').click(function() {
                const studentId = $(this).data('student-id');
                const studentName = $(this).data('student-name');
                $('#studentId').val(studentId);
                $('#studentName').text(studentName);
                modal.show();
            });

            span.click(function() { modal.hide(); });
            $(window).click(function(e) { if (e.target == modal[0]) modal.hide(); });

            $('.add-subject').click(function() {
                const newGroup = $('.subject-group:first').clone();
                newGroup.find('select, input').each(function() {
                    const name = $(this).attr('name').replace('[0]', `[${subjectCount}]`);
                    $(this).attr('name', name).val('');
                });
                newGroup.find('.remove-subject').show();
                newGroup.find('.course-selection').hide();
                newGroup.find('.all-courses').prop('checked', false);
                $('#subjectsContainer').append(newGroup);
                subjectCount++;
                filterCourses();
            });

            $(document).on('click', '.remove-subject', function() {
                if ($('.subject-group').length > 1) $(this).closest('.subject-group').remove();
            });

            $(document).on('change', '.subject-select', filterCourses);

            $(document).on('change', '.all-courses', function() {
                const group = $(this).closest('.subject-group');
                group.find('.course-selection').toggle(!this.checked);
            });

            function filterCourses() {
                $('.subject-group').each(function() {
                    const subject = $(this).find('.subject-select option:selected').text();
                    $(this).find('.course-option').each(function() {
                        $(this).toggle($(this).data('subject') === subject || subject === 'Select Subject');
                    });
                });
            }
        });
    </script>
</body>
</html>