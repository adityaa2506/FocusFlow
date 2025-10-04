<?php
session_start();
require_once '../config/database.php';

// --- HANDLE API REQUESTS (Desktop App) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
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
    exit();
}

// --- HANDLE BROWSER LOGIN ---
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username_web'])) {
    $username = trim($_POST['username_web']);
    $password = $_POST['password_web'];

    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['status'] === 'terminated') {
                $error_message = "Your account has been terminated by your manager.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
        $stmt->close();
    }
}

// Redirect if already logged in via browser
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
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
        <?php if (!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
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