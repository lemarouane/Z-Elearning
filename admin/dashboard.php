<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
log_error("L'administrateur $admin_id a accédé au tableau de bord");

// Récupérer les statistiques
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$validated_students = $conn->query("SELECT COUNT(*) FROM students WHERE is_validated = 1")->fetch_row()[0];
$pending_students = $conn->query("SELECT COUNT(*) FROM students WHERE is_validated = 0")->fetch_row()[0];
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$activity_count = $conn->query("SELECT COUNT(*) FROM activity_logs WHERE admin_id = $admin_id")->fetch_row()[0];

// Récupérer les étudiants récents
$students_result = $conn->query("SELECT id, name, email, is_validated, created_at FROM students ORDER BY created_at DESC LIMIT 5");

// Récupérer les cours récents
$courses_result = $conn->query("SELECT c.id, c.title, s.name AS subject, l.name AS level, c.created_at 
                                FROM courses c 
                                JOIN subjects s ON c.subject_id = s.id 
                                JOIN levels l ON c.level_id = l.id 
                                ORDER BY c.created_at DESC LIMIT 5");

// Récupérer les sujets pour le graphique
$subjects_result = $conn->query("SELECT s.name, COUNT(c.id) as course_count 
                                 FROM subjects s 
                                 LEFT JOIN courses c ON s.id = c.subject_id 
                                 GROUP BY s.id");
$subjects_data = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects_data[$row['name']] = $row['course_count'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord administrateur - Zouhair E-learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Tableau de bord administrateur</h1>
        <section class="stats">
            <div class="stat-card"><h3>Total des étudiants</h3><p><?php echo $total_students; ?></p></div>
            <div class="stat-card"><h3>Étudiants validés</h3><p><?php echo $validated_students; ?></p></div>
            <div class="stat-card"><h3>Étudiants en attente</h3><p><?php echo $pending_students; ?></p></div>
            <div class="stat-card"><h3>Total des cours</h3><p><?php echo $total_courses; ?></p></div>
            <div class="stat-card"><h3>Vos activités</h3><p><?php echo $activity_count; ?></p></div>
        </section>
        <section class="charts">
            <div class="chart-container"><h2>Statut des étudiants</h2><canvas id="studentChart"></canvas></div>
            <div class="chart-container"><h2>Cours par sujet</h2><canvas id="subjectChart" height="200"></canvas></div>
        </section>
        <section class="tables">
            <div class="table-container">
                <h2>Étudiants récents</h2>
                <table id="studentsTable" class="display">
                    <thead>
                        <tr><th>ID</th><th>Nom</th><th>Email</th><th>Validé</th><th>Inscrit</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['is_validated'] ? 'Oui' : 'Non'; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $row['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="manage_students.php" class="action-form" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn-action delete" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-container">
                <h2>Cours récents</h2>
                <table id="coursesTable" class="display">
                    <thead>
                        <tr><th>ID</th><th>Titre</th><th>Sujet</th><th>Niveau</th><th>Créé</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['level']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                    <a href="edit_course.php?id=<?php echo $row['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="manage_courses.php" class="action-form" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn-action delete" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        const studentCtx = document.getElementById('studentChart').getContext('2d');
        new Chart(studentCtx, {
            type: 'bar',
            data: {
                labels: ['Validés', 'En attente'],
                datasets: [{ label: 'Étudiants', data: [<?php echo $validated_students; ?>, <?php echo $pending_students; ?>], backgroundColor: ['#3498db', '#e74c3c'], borderWidth: 1 }]
            },
            options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
        });

        const subjectCtx = document.getElementById('subjectChart').getContext('2d');
        new Chart(subjectCtx, {
            type: 'pie',
            data: {
                labels: [<?php echo "'" . implode("','", array_keys($subjects_data)) . "'"; ?>],
                datasets: [{ data: [<?php echo implode(',', array_values($subjects_data)); ?>], backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'] }]
            },
            options: { plugins: { legend: { position: 'right' } }, maintainAspectRatio: false }
        });

        $(document).ready(function() {
            $('#studentsTable, #coursesTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: false
            });
        });
    </script>
</body>
</html>