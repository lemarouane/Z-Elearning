<?php
require_once '../includes/config.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level_id = sanitize_input($_POST['level_id']);
    $subject_id = sanitize_input($_POST['subject_id']);
    $difficulty = sanitize_input($_POST['difficulty']);

    $stmt = $conn->prepare("SELECT id, title FROM courses WHERE level_id = ? AND subject_id = ? AND difficulty = ?");
    $stmt->bind_param("iis", $level_id, $subject_id, $difficulty);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($course = $result->fetch_assoc()) {
            echo '<label><input type="checkbox" name="course_ids[]" value="' . $course['id'] . '"> ' . htmlspecialchars($course['title']) . '</label><br>';
        }
    } else {
        echo '<p>No courses available for this selection.</p>';
    }
    $stmt->close();
}
?>