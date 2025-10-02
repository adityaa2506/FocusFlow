<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Base SQL query
$sql = "SELECT application_name, COUNT(*) as count 
        FROM activity_log 
        WHERE user_id = ? AND is_idle = 0
        GROUP BY application_name 
        ORDER BY count DESC 
        LIMIT 5";

// Adjust query for managers
if ($role === 'manager') {
    // Check if filtering for a specific employee
    $employee_id_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;

    if ($employee_id_filter) {
        $user_id = $employee_id_filter; // Use the filtered employee ID
    } else {
        // If no filter, manager sees all employee data grouped together
        $sql = "SELECT al.application_name, COUNT(*) as count 
                FROM activity_log al
                JOIN users u ON al.user_id = u.id
                WHERE u.role = 'employee' AND al.is_idle = 0
                GROUP BY al.application_name 
                ORDER BY count DESC 
                LIMIT 5";
    }
}

$stmt = $conn->prepare($sql);

// Only bind a parameter if the query is for a specific user
if ($role !== 'manager' || isset($employee_id_filter)) {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$data = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['application_name'];
    $data[] = $row['count'];
}

$stmt->close();
$conn->close();

echo json_encode(['labels' => $labels, 'data' => $data]);
?>