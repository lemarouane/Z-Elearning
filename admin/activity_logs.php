<?php
require_once '../includes/config.php';
check_login('admin');

$admin_id = $_SESSION['admin_id'];
$logs_result = $conn->query("SELECT action, action_time, details FROM activity_logs WHERE admin_id = $admin_id ORDER BY action_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Zouhair E-learning</title>
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
                    <thead>
                        <tr><th>Action</th><th>Time</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $logs_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo $row['action_time']; ?></td>
                                <td><?php echo htmlspecialchars($row['details'] ?? 'N/A'); ?></td>
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
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
</body>
</html>