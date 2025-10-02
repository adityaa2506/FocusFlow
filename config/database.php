<?php
// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root'; // Use your MySQL root password if you have one
$db_name = 'focusflow';

// Create a new database connection object
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for connection errors and stop if any exist
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>