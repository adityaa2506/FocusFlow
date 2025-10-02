<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Get all possible data from the POST request
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$appName = isset($_POST['application_name']) ? $_POST['application_name'] : 'System'; // Default to 'System'
$windowTitle = isset($_POST['window_title']) ? $_POST['window_title'] : 'User Idle'; // Default to 'User Idle'
$isIdle = isset($_POST['is_idle']) ? (int)$_POST['is_idle'] : 0;

// The only required field is the user ID
if (empty($userId)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
    exit;
}

// SQL query now includes the is_idle column
$sql = "INSERT INTO activity_log (user_id, application_name, window_title, is_idle) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
// Bind all four parameters
$stmt->bind_param("issi", $userId, $appName, $windowTitle, $isIdle);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Activity logged successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to log activity.']);
}

$stmt->close();
$conn->close();
?>