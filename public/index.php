<?php
// 1. Include both the database and functions files
require_once '../config/database.php';
require_once '../core/functions.php'; // <-- ADD THIS LINE

// 2. Prepare and execute a query to fetch all activity
$sql = "SELECT id, application_name, window_title, tracked_at FROM activity_log ORDER BY tracked_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FocusFlow Dashboard</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h1>Activity Dashboard</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Application Name</th>
            <th>Window Title</th>
            <th>Time Tracked</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["application_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["window_title"]) . "</td>";
                // 3. Use our new function to format the timestamp
                echo "<td>" . formatTimestamp($row["tracked_at"]) . "</td>"; // <-- CHANGE THIS LINE
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No activity logged yet.</td></tr>";
        }
        $conn->close();
        ?>
    </table>

</body>
</html>