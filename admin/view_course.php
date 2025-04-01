<?php
require_once '../includes/config.php';
check_login('admin');

$course_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
if (!$course_id) {
    header("Location: manage_courses.php");
    exit();
}

$course = $conn->query("SELECT c.title, c.content_type, c.content_path, c.created_at, s.name AS subject, l.name AS level 
                        FROM courses c 
                        JOIN subjects s ON c.subject_id = s.id 
                        JOIN levels l ON c.level_id = l.id 
                        WHERE c.id = $course_id")->fetch_assoc();
if (!$course) {
    header("Location: manage_courses.php");
    exit();
}

$embed_url = '';
if ($course['content_type'] === 'video') {
    if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $course['content_path'], $match)) {
        $embed_url = "https://www.youtube.com/embed/{$match[1]}?rel=0&modestbranding=1&autoplay=0";
    } else {
        $embed_url = $course['content_path'];
        log_error("Video URL not recognized as YouTube: {$course['content_path']}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script type="module">
        import { getDocument, GlobalWorkerOptions } from '../assets/js/pdfjs/pdf.mjs';
        GlobalWorkerOptions.workerSrc = '../assets/js/pdfjs/pdf.worker.mjs';
        window.pdfjsLib = { getDocument };
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <div class="course-view-container">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <div class="course-meta">
                <span class="meta-item"><i class="fas fa-book"></i> <?php echo htmlspecialchars($course['subject']); ?></span>
                <span class="meta-item"><i class="fas fa-signal"></i> <?php echo htmlspecialchars($course['level']); ?></span>
                <span class="meta-item"><i class="fas fa-clock"></i> <?php echo $course['created_at']; ?></span>
            </div>
            <div class="course-content">
                <div class="content-preview">
                    <?php if ($course['content_type'] === 'pdf'): ?>
                        <div class="pdf-wrapper">
                            <div class="pdf-controls">
                                <button id="zoomIn" class="btn-action"><i class="fas fa-search-plus"></i></button>
                                <button id="zoomOut" class="btn-action"><i class="fas fa-search-minus"></i></button>
                            </div>
                            <div id="pdfContainer" class="pdf-container"></div>
                            <script type="module">
                                const url = 'stream_pdf.php?file=<?php echo urlencode($course['content_path']); ?>';
                                let pdfDoc = null;
                                let scale = 1.5;

                                async function renderAllPages() {
                                    const pdfContainer = document.getElementById('pdfContainer');
                                    pdfContainer.innerHTML = ''; // Clear previous content
                                    for (let num = 1; num <= pdfDoc.numPages; num++) {
                                        const page = await pdfDoc.getPage(num);
                                        const viewport = page.getViewport({ scale });
                                        const canvas = document.createElement('canvas');
                                        canvas.className = 'pdf-page';
                                        pdfContainer.appendChild(canvas);
                                        const ctx = canvas.getContext('2d');
                                        canvas.height = viewport.height;
                                        canvas.width = viewport.width;
                                        const renderTask = page.render({ canvasContext: ctx, viewport });
                                        await renderTask.promise;
                                    }
                                    console.log('All pages rendered at scale:', scale);
                                }

                                window.pdfjsLib.getDocument(url).promise.then(pdf => {
                                    pdfDoc = pdf;
                                    renderAllPages();
                                }).catch(err => console.error('Error loading PDF:', err));

                                document.getElementById('zoomIn').addEventListener('click', () => {
                                    scale += 0.25;
                                    console.log('Zoom in clicked, new scale:', scale);
                                    renderAllPages();
                                });

                                document.getElementById('zoomOut').addEventListener('click', () => {
                                    if (scale > 0.25) {
                                        scale -= 0.25;
                                        console.log('Zoom out clicked, new scale:', scale);
                                        renderAllPages();
                                    }
                                });
                            </script>
                        </div>
                    <?php elseif ($course['content_type'] === 'video'): ?>
                        <div class="video-wrapper">
                            <?php if ($embed_url): ?>
                                <iframe src="<?php echo htmlspecialchars($embed_url); ?>" class="embedded-video" frameborder="0" allowfullscreen></iframe>
                            <?php else: ?>
                                <p class="error">Invalid video URL: <?php echo htmlspecialchars($course['content_path']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="course-actions">
                    <a href="manage_courses.php" class="btn-action"><i class="fas fa-arrow-left"></i> Back to Courses</a>
                </div>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/admin.js"></script>
</body>
</html>