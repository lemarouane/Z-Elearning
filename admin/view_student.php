<?php if ($course['content_type'] === 'pdf'): ?>
    <div id="pdf-container" class="pdf-viewer-container"></div>
    <script src="../assets/pdfjs/pdf.mjs"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pdfPath = '../uploads/<?php echo htmlspecialchars($course['content_path']); ?>';
        
        pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = '../assets/pdfjs/pdf.worker.mjs';

        let loadingTask = pdfjsLib.getDocument(pdfPath);
        loadingTask.promise.then(function(pdf) {
            console.log('PDF loaded');
            
            // Fetch the first page
            pdf.getPage(1).then(function(page) {
                console.log('Page loaded');
                
                let scale = 1.5;
                let viewport = page.getViewport({scale: scale});

                // Prepare canvas
                let canvas = document.createElement('canvas');
                let context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                let renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                let renderTask = page.render(renderContext);
                renderTask.promise.then(function() {
                    console.log('Page rendered');
                    document.getElementById('pdf-container').appendChild(canvas);
                });
            });
        }).catch(function(reason) {
            console.error('PDF loading error:', reason);
            document.getElementById('pdf-container').innerHTML = 
                '<p class="error">Failed to load PDF. Please try again later.</p>';
        });
    });
    </script>
<?php endif; ?>     

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <div class="student-view-container">
            <h1><?php echo htmlspecialchars($student['full_name']); ?></h1>
            <div class="student-details">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
                <p><strong>Device Token:</strong> <?php echo htmlspecialchars($student['device_token'] ?? 'Not set'); ?></p>
                <p><strong>Validated:</strong> <?php echo $student['is_validated'] ? 'Yes' : 'No'; ?></p>
                <p><strong>Joined:</strong> <?php echo $student['created_at']; ?></p>
            </div>
            <div class="enrolled-courses">
                <h2>Enrolled Courses</h2>
                <table class="display">
                    <thead><tr><th>ID</th><th>Title</th><th>Progress</th></tr></thead>
                    <tbody>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo $course['progress']; ?>%</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="screenshot-logs">
                <h2>Recent Screenshots</h2>
                <table class="display">
                    <thead><tr><th>Course ID</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        <?php while ($screenshot = $screenshots->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $screenshot['course_id']; ?></td>
                                <td><?php echo $screenshot['timestamp']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <a href="edit_student.php?id=<?php echo $student_id; ?>" class="btn-action edit">Edit Student</a>
            <a href="manage_students.php" class="btn-action delete">Back</a>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>