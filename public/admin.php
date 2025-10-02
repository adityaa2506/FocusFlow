<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit();
}
require_once '../config/database.php';
require_once '../core/functions.php';

// Handle user reactivation
if (isset($_GET['reactivate_user_id'])) {
    $user_id_to_reactivate = (int)$_GET['reactivate_user_id'];
    $sql = "UPDATE users SET status = 'active' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id_to_reactivate);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?view=terminated"); // Redirect back to the terminated list
    exit();
}

// Handle user termination
if (isset($_GET['terminate_user_id'])) {
    $user_id_to_terminate = (int)$_GET['terminate_user_id'];
    if ($user_id_to_terminate !== (int)$_SESSION['user_id']) {
        $sql = "UPDATE users SET status = 'terminated' WHERE id = ? AND role = 'employee'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id_to_terminate);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin.php");
    exit();
}

// Handle form submission to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id_to_update = (int)$_POST['user_id'];
    $interval = !empty($_POST['interval']) ? (int)$_POST['interval'] : NULL;
    $idle = !empty($_POST['idle']) ? (int)$_POST['idle'] : NULL;
    
    $sql = "UPDATE users SET tracking_interval = ?, idle_timeout = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $interval, $idle, $user_id_to_update);
    $stmt->execute();
    $stmt->close();
}

// Determine which view to show: 'active' or 'terminated'
$view = isset($_GET['view']) && $_GET['view'] === 'terminated' ? 'terminated' : 'active';

// Fetch users based on the selected view
$sql = "SELECT id, username, email, role, tracking_interval, idle_timeout FROM users WHERE status = ? ORDER BY username ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $view);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - FocusFlow</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .view-filters { margin-bottom: 20px; }
        .view-filters a { text-decoration: none; padding: 8px 15px; border-radius: 5px; background-color: #e9ecef; color: #495057; font-weight: 500; margin-right: 10px; }
        .view-filters a.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h1>User Management</h1>

        <div class="view-filters">
            <a href="admin.php" class="<?php echo $view === 'active' ? 'active' : ''; ?>">Active Users</a>
            <a href="admin.php?view=terminated" class="<?php echo $view === 'terminated' ? 'active' : ''; ?>">Terminated Users</a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Interval (s)</th>
                        <th>Idle Timeout (s)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["username"]); ?></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td><?php echo htmlspecialchars($row["role"]); ?></td>
                            <form action="admin.php?view=<?php echo $view; ?>" method="POST">
                                <td>
                                    <input type="number" name="interval" placeholder="Global" value="<?php echo htmlspecialchars($row["tracking_interval"]); ?>">
                                </td>
                                <td>
                                    <input type="number" name="idle" placeholder="Global" value="<?php echo htmlspecialchars($row["idle_timeout"]); ?>">
                                </td>
                                <td>
                                    <div class="actions-form">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-update">Update</button>
                                        
                                        <?php if ($view === 'active' && $row['role'] === 'employee'): ?>
                                            <a href="admin.php?terminate_user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-terminate" 
                                               onclick="return confirm('Are you sure you want to terminate this user?');">
                                               Terminate
                                            </a>
                                        <?php elseif ($view === 'terminated'): ?>
                                            <a href="admin.php?reactivate_user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-update" 
                                               onclick="return confirm('Are you sure you want to reactivate this user?');">
                                               Reactivate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>