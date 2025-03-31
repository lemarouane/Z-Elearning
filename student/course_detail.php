<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

define('UPLOADS_DIR', dirname(__DIR__) . '/uploads/');
define('PDF_DIR', UPLOADS_DIR . 'pdfs/');

$course_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT c.title, c.content_type, c.content_path, s.name AS subject, l.name AS level 
                        FROM courses c 
                        JOIN subjects s ON c.subject_id = s.id 
                        JOIN levels l ON c.level_id = l.id 
                        JOIN enrollments e ON c.id = e.course_id 
                        WHERE c.id = ? AND e.student_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION['student_id']);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: view_course.php");
    exit();
}

$base_url = 'http://localhost/elearning/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course['title']; ?> - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/student.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Zouhair E-learning</h2>
        <a href="view_course.php" class="active">My Courses</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="dashboard-container">
        <h2><?php echo $course['title']; ?></h2>
        <p>Subject: <?php echo $course['subject']; ?></p>
        <p>Level: <?php echo $course['level']; ?></p>
        <div class="course-content">
            <?php if ($course['content_type'] == 'pdf'): ?>
                <?php 
                $pdf_path = PDF_DIR . $course['content_path'];
                $pdf_url = $base_url . 'uploads/pdfs/' . rawurlencode($course['content_path']);
                if (file_exists($pdf_path)): ?>
                    <div id="pdf-viewer" style="width: 100%; height: 600px; overflow-y: auto;"></div>
                    <script>
                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
                        const pdfUrl = '<?php echo $pdf_url; ?>';
                        console.log('Loading PDF from: ' + pdfUrl);
                        pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
                            const viewer = document.getElementById('pdf-viewer');
                            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                                pdf.getPage(pageNum).then(page => {
                                    const canvas = document.createElement('canvas');
                                    viewer.appendChild(canvas);
                                    const context = canvas.getContext('2d');
                                    const viewport = page.getViewport({ scale: 1.5 });
                                    canvas.height = viewport.height;
                                    canvas.width = viewport.width;
                                    page.render({ canvasContext: context, viewport: viewport }).promise.then(() => {
                                        console.log('Page ' + pageNum + ' rendered');
                                    });
                                }).catch(error => {
                                    viewer.innerHTML = '<p class="error">Failed to load PDF page: ' + error.message + '</p>';
                                });
                            }
                        }).catch(error => {
                            document.getElementById('pdf-viewer').innerHTML = '<p class="error">Failed to load PDF: ' + error.message + ' (URL: ' + pdfUrl + ')</p>';
                        });
                    </script>
                <?php else: ?>
                    <p class="error">PDF file not found at: <?php echo $pdf_path; ?></p>
                <?php endif; ?>
            <?php elseif ($course['content_type'] == 'video'): ?>
                <?php if (preg_match('/^https:\/\/www\.youtube\.com\/embed\/[a-zA-Z0-9_-]+$/', $course['content_path'])): ?>
                    <iframe width="100%" height="400px" src="<?php echo htmlspecialchars($course['content_path']); ?>" frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <p class="error">Invalid YouTube embed URL: <?php echo htmlspecialchars($course['content_path']); ?>. Please use format: https://www.youtube.com/embed/VIDEO_ID</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <a href="view_course.php" class="btn">Back to Courses</a>
    </div>
</body>
</html>