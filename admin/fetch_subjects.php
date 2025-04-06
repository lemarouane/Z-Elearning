<?php
require_once '../includes/config.php';

$level_id = isset($_POST['level_id']) ? intval($_POST['level_id']) : 0;
$options = '<option value="">Select Subject</option>';

if ($level_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM subjects WHERE level_id = ? OR level_id IS NULL");
    $stmt->bind_param("i", $level_id);
    $stmt->execute();
    $subjects = $stmt->get_result();
    while ($subject = $subjects->fetch_assoc()) {
        $options .= "<option value='{$subject['id']}'>" . htmlspecialchars($subject['name']) . "</option>";
    }
    $stmt->close();
}

echo $options;
?>