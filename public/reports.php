<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';
require_once '../core/functions.php';

$user_role = $_SESSION['role'];
$employees = [];
$selected_employee_id = null;
$page_title = "My Reports";

// If the user is a manager, fetch the list of employees for the dropdown
if ($user_role === 'manager') {
    $page_title = "Employee Reports";
    $employee_result = $conn->query("SELECT id, username FROM users WHERE role = 'employee'");
    while ($row = $employee_result->fetch_assoc()) {
        $employees[] = $row;
    }
    // Check if a specific employee is being filtered via URL
    if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
        $selected_employee_id = (int)$_GET['employee_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FocusFlow</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .filter-form { margin-bottom: 20px; }
        .chart-container {
            width: 100%;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if ($user_role === 'manager'): ?>
        <div class="card">
            <form action="reports.php" method="GET" class="filter-form">
                <label for="employee_id">View Report For:</label>
                <select name="employee_id" id="employee_id" onchange="this.form.submit()">
                    <option value="">-- Select an Employee --</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo $employee['id']; ?>" <?php if ($selected_employee_id == $employee['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($employee['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="chart-container">
            <h2>Top 5 Applications Used</h2>
            <canvas id="appUsageChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const employeeId = urlParams.get('employee_id');
            let apiUrl = 'api/chart_data.php';

            // If a manager is filtering for an employee, add it to the API URL
            if (employeeId) {
                apiUrl += '?employee_id=' + employeeId;
            }

            // Only fetch and render the chart if it's an employee view or a manager has selected an employee
            <?php if ($user_role === 'employee' || $selected_employee_id): ?>
            fetch(apiUrl)
                .then(response => response.json())
                .then(chartData => {
                    const ctx = document.getElementById('appUsageChart').getContext('2d');
                    
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Activity Count',
                                data: chartData.data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.7)',
                                    'rgba(54, 162, 235, 0.7)',
                                    'rgba(255, 206, 86, 0.7)',
                                    'rgba(75, 192, 192, 0.7)',
                                    'rgba(153, 102, 255, 0.7)'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        }
                    });
                })
                .catch(error => console.error('Error fetching chart data:', error));
            <?php else: ?>
                <?php if ($user_role === 'manager'): ?>
                    // If manager hasn't selected an employee, show a message
                    const ctx = document.getElementById('appUsageChart');
                    ctx.parentElement.innerHTML = '<p style="text-align: center; color: #6c757d;">Please select an employee to view their report.</p>';
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>