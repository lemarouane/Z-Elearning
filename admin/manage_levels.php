<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (isset($_POST['add_level'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $stmt = $conn->prepare("INSERT INTO levels (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            $level_id = $conn->insert_id;
            $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Added level', 'Level ID: $level_id')");
            $message = "Level '$name' added successfully!";
            regenerate_csrf_token();
        } else {
            $error = "Failed to add level: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['delete_level'])) {
        $level_id = sanitize_input($_POST['level_id']);
        $stmt = $conn->prepare("DELETE FROM levels WHERE id = ?");
        $stmt->bind_param("i", $level_id);
        if ($stmt->execute()) {
            $conn->query("INSERT INTO activity_logs (user_id, user_role, action, details) VALUES ($admin_id, 'admin', 'Deleted level', 'Level ID: $level_id')");
            $message = "Level deleted successfully!";
            regenerate_csrf_token();
        } else {
            $error = "Failed to delete level: " . $conn->error;
        }
        $stmt->close();
    }
}

$levels = $conn->query("SELECT id, name, description, created_at FROM levels ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Levels - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Manage Levels</h1>
        <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <section class="add-level">
            <h2>Add New Level</h2>
            <form method="POST" class="course-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="name">Level Name (e.g., Bac+4)</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit" name="add_level" class="btn-action add">Add Level</button>
            </form>
        </section>
        <section class="tables">
            <div class="table-container">
                <h2>Existing Levels</h2>
                <table id="levelsTable" class="display">
                    <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($level = $levels->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $level['id']; ?></td>
                                <td><?php echo htmlspecialchars($level['name']); ?></td>
                                <td><?php echo htmlspecialchars($level['description'] ?? 'N/A'); ?></td>
                                <td><?php echo $level['created_at']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="level_id" value="<?php echo $level['id']; ?>">
                                        <button type="submit" name="delete_level" class="btn-action delete" onclick="return confirm('Are you sure? This may affect related courses.');"><i class="fas fa-trash"></i></button>
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
        $(document).ready(function() {
            $('#levelsTable').DataTable({ pageLength: 10, order: [[1, 'asc']] });
        });
    </script>
</body>
</html>