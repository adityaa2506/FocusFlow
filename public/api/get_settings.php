<?php
// Set the content type header to indicate that the response is JSON
header('Content-Type: application/json');

// Include the database connection
require_once '../../config/database.php';

// Fetch all settings from the database
$result = $conn->query("SELECT setting_key, setting_value FROM settings");

$settings = [];
if ($result) {
    // Loop through the results and create an associative array
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$conn->close();

// Encode the array into a JSON string and output it
echo json_encode($settings);
?>