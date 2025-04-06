<?php
require_once '../includes/config.php';
check_login('student');

$student_id = $_SESSION['student_id'];
$file = isset($_GET['file']) ? sanitize_input($_GET['file']) : null;

if (!$file) {
    header('HTTP/1.1 400 Bad Request');
    echo "No file specified.";
    exit();
}

// Verify student has access to the course
$file_path = "../uploads/$file";
$query = "
    SELECT c.id 
    FROM courses c 
    LEFT JOIN user_courses uc ON c.id = uc.course_id AND uc.student_id = $student_id
    LEFT JOIN student_subjects ss ON c.subject_id = ss.subject_id AND c.level_id = ss.level_id AND ss.student_id = $student_id
    WHERE c.content_path = ? 
    AND (uc.student_id = $student_id OR (ss.student_id = $student_id AND (ss.difficulty IS NULL OR c.difficulty = ss.difficulty)))
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $file);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0 || !file_exists($file_path)) {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied or file not found.";
    exit();
}

// Stream the PDF
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($file_path));
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$stream = fopen($file_path, 'rb');
while (!feof($stream)) {
    echo fread($stream, 8192);
    flush();
}
fclose($stream);
exit();
?>