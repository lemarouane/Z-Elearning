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
                                <button id="zoomIn" class="btn-action"><i class="fas fa-search-plus"></i> Zoom In</button>
                                <button id="zoomOut" class="btn-action"><i class="fas fa-search-minus"></i> Zoom Out</button>
                                <button id="fitWidth" class="btn-action"><i class="fas fa-arrows-alt-h"></i> Fit Width</button>
                                <span id="pageInfo">Pages: <span id="pageCount">0</span></span>
                            </div>
                            <div id="pdfContainer" class="pdf-container">
                                <!-- PDF pages will be rendered here -->
                            </div>
                            <script type="module">
                                const url = 'stream_pdf.php?file=<?php echo urlencode($course['content_path']); ?>';
                                let pdfDoc = null;
                                let scale = 1.2;
                                const pdfContainer = document.getElementById('pdfContainer');
                                const pageCount = document.getElementById('pageCount');
                                
                                // Calculate the best scale to fit the width
                                function calculateFitToWidthScale(page) {
                                    const containerWidth = pdfContainer.clientWidth - 20; // -20 for padding
                                    const viewportOriginal = page.getViewport({ scale: 1.0 });
                                    return containerWidth / viewportOriginal.width;
                                }

                                // Render all pages of the PDF
                                async function renderAllPages() {
                                    pdfContainer.innerHTML = ''; // Clear container
                                    
                                    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                                        const page = await pdfDoc.getPage(pageNum);
                                        const viewport = page.getViewport({ scale });
                                        
                                        // Create page container
                                        const pageContainer = document.createElement('div');
                                        pageContainer.className = 'pdf-page-container';
                                        pageContainer.id = `page-${pageNum}`;
                                        
                                        // Create page number label
                                        const pageLabel = document.createElement('div');
                                        pageLabel.className = 'page-number';
                                        pageLabel.textContent = `Page ${pageNum}`;
                                        pageContainer.appendChild(pageLabel);
                                        
                                        // Create canvas for this page
                                        const canvas = document.createElement('canvas');
                                        canvas.className = 'pdf-page';
                                        pageContainer.appendChild(canvas);
                                        pdfContainer.appendChild(pageContainer);
                                        
                                        const ctx = canvas.getContext('2d');
                                        canvas.height = viewport.height;
                                        canvas.width = viewport.width;
                                        
                                        // Render the page
                                        await page.render({
                                            canvasContext: ctx,
                                            viewport: viewport
                                        }).promise;
                                    }
                                }
                                
                                // Initialize PDF.js
                                window.pdfjsLib.getDocument(url).promise
                                    .then(async (pdf) => {
                                        pdfDoc = pdf;
                                        pageCount.textContent = pdfDoc.numPages;
                                        
                                        // Get first page to calculate initial scale for fit-to-width
                                        const firstPage = await pdfDoc.getPage(1);
                                        scale = calculateFitToWidthScale(firstPage);
                                        
                                        renderAllPages();
                                    })
                                    .catch(error => {
                                        console.error('Error loading PDF:', error);
                                        pdfContainer.innerHTML = `<div class="error-message">Failed to load PDF: ${error.message}</div>`;
                                    });
                                
                                // Event listeners for zoom controls
                                document.getElementById('zoomIn').addEventListener('click', () => {
                                    scale += 0.2;
                                    renderAllPages();
                                });
                                
                                document.getElementById('zoomOut').addEventListener('click', () => {
                                    if (scale > 0.5) {
                                        scale -= 0.2;
                                        renderAllPages();
                                    }
                                });
                                
                                document.getElementById('fitWidth').addEventListener('click', async () => {
                                    const firstPage = await pdfDoc.getPage(1);
                                    scale = calculateFitToWidthScale(firstPage);
                                    renderAllPages();
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