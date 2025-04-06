<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$logs = $conn->query("SELECT user_id, user_role, action, details, action_time FROM activity_logs WHERE user_role = 'admin' AND user_id = $admin_id ORDER BY action_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Zouhair E-Learning</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="dashboard">
        <h1>Activity Logs</h1>
        <section class="tables">
            <div class="table-container">
                <h2>Your Activities</h2>
                <table id="logsTable" class="display">
                    <thead><tr><th>User ID</th><th>Role</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $log['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($log['user_role']); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['details'] ?? 'N/A'); ?></td>
                                <td><?php echo $log['action_time']; ?></td>
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
            $('#logsTable').DataTable({
                pageLength: 10,
                order: [[4, 'desc']],
                language: { search: "Filter logs:" }
            });
        });
    </script>
</body>
</html>