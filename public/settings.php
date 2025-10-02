<?php
session_start();
// Protection: only managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit();
}

require_once '../config/database.php';

$message = '';
// Handle form submission to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking_interval = (int)$_POST['tracking_interval'];
    $idle_timeout = (int)$_POST['idle_timeout'];

    // Update the database using prepared statements
    $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ss", $tracking_interval, $key1);
    $key1 = 'tracking_interval_seconds';
    $stmt->execute();
    
    $stmt->bind_param("ss", $idle_timeout, $key2);
    $key2 = 'idle_timeout_seconds';
    $stmt->execute();

    $stmt->close();
    $message = "Settings updated successfully!";
}

// Fetch current settings to display in the form
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - FocusFlow</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Application Settings</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="tracking_interval">Tracking Interval (in seconds)</label>
                    <input type="number" id="tracking_interval" name="tracking_interval" value="<?php echo htmlspecialchars($settings['tracking_interval_seconds']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="idle_timeout">Idle Timeout (in seconds)</label>
                    <input type="number" id="idle_timeout" name="idle_timeout" value="<?php echo htmlspecialchars($settings['idle_timeout_seconds']); ?>" required>
                </div>
                <button type="submit">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html>