<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit();
}
require_once '../config/database.php';

// Fetch all terminated users to display
$sql = "SELECT id, username, email FROM users WHERE status = 'terminated' ORDER BY username ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminated Users - FocusFlow</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h1>Terminated Users</h1>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["username"]); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td>
                                    <a href="admin.php?reactivate_user_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-update" 
                                       onclick="return confirm('Are you sure you want to reactivate this user?');">
                                       Reactivate
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No terminated users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>