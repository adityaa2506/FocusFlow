<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../core/functions.php';

$current_user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$page_title = ($user_role === 'manager') ? "Manager Dashboard" : "My Dashboard";

// --- PAGINATION & FILTER LOGIC ---
$records_per_page = 25;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$employees = [];
$selected_employee_id = null;
$time_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_params_for_url = "&filter=$time_filter"; // To carry filters in pagination links

if ($user_role === 'manager') {
    $employee_result = $conn->query("SELECT id, username FROM users WHERE role = 'employee'");
    while ($row = $employee_result->fetch_assoc()) {
        $employees[] = $row;
    }
    if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
        $selected_employee_id = (int)$_GET['employee_id'];
        $filter_params_for_url .= "&employee_id=$selected_employee_id";
    }
}

// Build WHERE clause for all SQL queries based on filters
$where_clauses = [];
$params = [];
$types = '';

if ($user_role === 'manager') {
    $where_clauses[] = "u.role = 'employee'";
    if ($selected_employee_id) {
        $where_clauses[] = "al.user_id = ?";
        $params[] = $selected_employee_id;
        $types .= 'i';
    }
} else { // Employee sees only their own data
    $where_clauses[] = "al.user_id = ?";
    $params[] = $current_user_id;
    $types .= 'i';
}

if ($time_filter == 'today') {
    $where_clauses[] = "DATE(al.tracked_at) = CURDATE()";
} elseif ($time_filter == 'week') {
    $where_clauses[] = "al.tracked_at >= DATE(NOW()) - INTERVAL 7 DAY";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

// --- STATS CALCULATION ---
$total_logs = 0;
$idle_events = 0;
$active_users = 0;

if ($user_role === 'manager') {
    // Total Logs for the filtered view
    $sql_total = "SELECT COUNT(*) as count FROM activity_log al JOIN users u ON al.user_id = u.id $where_sql";
    $stmt_total = $conn->prepare($sql_total);
    if (count($params) > 0) $stmt_total->bind_param($types, ...$params);
    $stmt_total->execute();
    $total_logs = $stmt_total->get_result()->fetch_assoc()['count'];
    $stmt_total->close();

    // Idle Events for the filtered view
    $sql_idle_where = $where_sql ? $where_sql . " AND al.is_idle = 1" : "WHERE al.is_idle = 1";
    $sql_idle = "SELECT COUNT(*) as count FROM activity_log al JOIN users u ON al.user_id = u.id $sql_idle_where";
    $stmt_idle = $conn->prepare($sql_idle);
    if (count($params) > 0) $stmt_idle->bind_param($types, ...$params);
    $stmt_idle->execute();
    $idle_events = $stmt_idle->get_result()->fetch_assoc()['count'];
    $stmt_idle->close();
    
    // Active Users Today (this stat ignores filters to show all active users)
    $sql_active = "SELECT COUNT(DISTINCT user_id) as count FROM activity_log WHERE DATE(tracked_at) = CURDATE()";
    $active_users = $conn->query($sql_active)->fetch_assoc()['count'];
}

// --- Calculate total records for pagination using the same filters ---
$sql_count = "SELECT COUNT(*) as total FROM activity_log al JOIN users u ON al.user_id = u.id $where_sql";
$stmt_count = $conn->prepare($sql_count);
if (count($params) > 0) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_records = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
$stmt_count->close();

// --- Fetch main table data with LIMIT and OFFSET for the current page ---
$sql_table = "SELECT u.username, al.id, al.application_name, al.window_title, al.tracked_at, al.is_idle 
              FROM activity_log al JOIN users u ON al.user_id = u.id $where_sql 
              ORDER BY al.tracked_at DESC LIMIT ? OFFSET ?";
$stmt_table = $conn->prepare($sql_table);
$limit_params = array_merge($params, [$records_per_page, $offset]);
$types .= 'ii';
$stmt_table->bind_param($types, ...$limit_params);
$stmt_table->execute();
$result = $stmt_table->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FocusFlow</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if ($user_role === 'manager'): ?>
        <div class="stat-cards">
            <div class="stat-card">
                <h3>Total Logged Events (Filtered)</h3>
                <p class="stat-value"><?php echo number_format($total_logs); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Idle Events (Filtered)</h3>
                <p class="stat-value"><?php echo number_format($idle_events); ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Users Today</h3>
                <p class="stat-value"><?php echo $active_users; ?></p>
            </div>
        </div>

        <div class="card">
            <div class="filter-bar">
                <div class="time-filters">
                    <a href="?filter=all&employee_id=<?php echo $selected_employee_id; ?>" class="<?php echo $time_filter == 'all' ? 'active' : ''; ?>">All Time</a>
                    <a href="?filter=today&employee_id=<?php echo $selected_employee_id; ?>" class="<?php echo $time_filter == 'today' ? 'active' : ''; ?>">Today</a>
                    <a href="?filter=week&employee_id=<?php echo $selected_employee_id; ?>" class="<?php echo $time_filter == 'week' ? 'active' : ''; ?>">This Week</a>
                </div>
                <form action="dashboard.php" method="GET" class="filter-form">
                    <input type="hidden" name="filter" value="<?php echo $time_filter; ?>">
                    <select name="employee_id" onchange="this.form.submit()">
                        <option value="">View All Employees</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>" <?php if ($selected_employee_id == $employee['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($employee['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>Activity Log</h2>
            <table>
                <thead>
                    <tr>
                        <?php if ($user_role === 'manager'): ?>
                            <th>Username</th>
                        <?php endif; ?>
                        <th>Application Name</th>
                        <th>Window Title</th>
                        <th>Time Tracked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $rowClass = $row['is_idle'] ? 'class="idle-row"' : '';
                            echo "<tr " . $rowClass . ">";
                            if ($user_role === 'manager') {
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            }
                            echo "<td>" . htmlspecialchars($row["application_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["window_title"]) . "</td>";
                            echo "<td>" . formatTimestamp($row["tracked_at"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No activity logged for the selected filter.</td></tr>";
                    }
                    $stmt_table->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo $filter_params_for_url; ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <a href="?page=<?php echo $page; ?><?php echo $filter_params_for_url; ?>" 
                       class="<?php echo $page == $current_page ? 'active' : ''; ?>">
                       <?php echo $page; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?><?php echo $filter_params_for_url; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>