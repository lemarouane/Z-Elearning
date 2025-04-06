<?php
require_once __DIR__ . '/config.php';

$role = isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['student_id']) ? 'student' : 'guest');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/<?php echo $role === 'admin' ? 'admin' : 'student'; ?>.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Zouhair E-learning</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <?php if ($role === 'admin'): ?>
                    <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></a></li>
                    <li><a href="../admin/manage_students.php"><i class="fas fa-users"></i> <span>Gérer les étudiants</span></a></li>
                    <!-- Inside <nav class="sidebar-nav"> -->
<li><a href="../admin/manage_levels.php"><i class="fas fa-layer-group"></i> <span>Gérer les niveaux</span></a></li>
<!-- Add this after "Gérer les cours" -->
                    <li><a href="../admin/manage_courses.php"><i class="fas fa-book"></i> <span>Gérer les cours</span></a></li>
                    
                    <li><a href="../admin/add_course.php"><i class="fas fa-plus-circle"></i> <span>Ajouter un cours</span></a></li>
                    <li><a href="../admin/activity_logs.php"><i class="fas fa-history"></i> <span>Journal d'activités</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
                <?php elseif ($role === 'student'): ?>
                    <li><a href="../student/view_course.php"><i class="fas fa-book-open"></i> <span>Mes cours</span></a></li>
                    <li><a href="../student/profile.php"><i class="fas fa-user"></i> <span>Profil</span></a></li>
                    <li><a href="../student/quiz.php"><i class="fas fa-question-circle"></i> <span>Quiz</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
                <?php else: ?>
                    <li><a href="../admin/login.php"><i class="fas fa-user-shield"></i> <span>Connexion administrateur</span></a></li>
                    <li><a href="../student/login.php"><i class="fas fa-user"></i> <span>Connexion étudiant</span></a></li>
                    <li><a href="../student/register.php"><i class="fas fa-user-plus"></i> <span>S'inscrire</span></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    <div class="main-content">