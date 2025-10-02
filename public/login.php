<?php
session_start();
require_once '../config/database.php';

// --- HANDLE API REQUESTS FIRST ---
// This block will run if the request is from your desktop app
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username'])) {
    header('Content-Type: application/json');
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit();
    }

    $sql = "SELECT id, username, password, role, status, tracking_interval, idle_timeout FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'terminated') {
            echo json_encode(['status' => 'error', 'message' => 'Your account has been terminated.']);
        } elseif (password_verify($password, $user['password'])) {
            echo json_encode([
                'status' => 'success',
                'user_id' => $user['id'],
                'username' => $user['username'],
                'tracking_interval' => $user['tracking_interval'],
                'idle_timeout' => $user['idle_timeout']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }
    $stmt->close();
    $conn->close();
    exit(); // Stop the script here for API calls
}

// --- HANDLE BROWSER USERS ---
// This part will only run for users visiting the page in a web browser
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = ''; // For displaying errors on the HTML form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FocusFlow</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="form-container">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username_web">Username</label>
                <input type="text" id="username_web" name="username_web" required>
            </div>
            <div class="form-group">
                <label for="password_web">Password</label>
                <input type="password" id="password_web" name="password_web" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>