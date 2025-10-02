# FocusFlow - Web Dashboard

FocusFlow is a comprehensive web application designed for managers and employees to monitor and analyze application usage data collected by its companion desktop agent. It provides role-based access, detailed activity logs, and data visualizations to offer insights into productivity.



---

## ✨ Features

- **Role-Based Access**: Separate, secure dashboards for managers and employees.
- **Manager Dashboard**:
    - View activity logs for all employees.
    - Filter activity by employee, and by time periods (Today, This Week, All Time).
    - At-a-glance summary statistics for total logs, idle events, and active users.
    - Terminate and reactivate employee accounts without losing historical data.
- **Reports Page**:
    - Visual charts (Pie, Bar, Doughnut) summarizing application usage, activity by hour, and idle vs. active time.
    - Managers can view reports for any specific employee.
- **Admin & Settings**:
    - Managers can update global settings like tracking intervals and idle timeouts.
    - Managers can set user-specific intervals and timeouts for individual employees.
- **Modern UI**: A clean, responsive, card-based interface for easy navigation and data consumption.

---

## 🛠️ Technology Stack

- **Backend**: Core PHP
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Charting Library**: Chart.js
- **Web Server**: Apache (via XAMPP)

---

## 🚀 Getting Started

### Prerequisites

- A web server environment like [XAMPP](https://www.apachefriends.org/index.html) or WAMP.
- MySQL database.

### Installation & Setup

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/adityaa2506/FocusFlow/
    ```
2.  **Move to Web Root**: Place the `FocusFlow` project folder inside your server's web root directory (e.g., `C:/xampp/htdocs/`).

3.  **Database Setup**:
    - Open a MySQL client like phpMyAdmin.
    - Create a new database named `focusflow`.
    - Import the following SQL schema to set up all the necessary tables:

    ```sql
    -- Users Table
    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('employee','manager') NOT NULL DEFAULT 'employee',
      `status` enum('active','terminated') NOT NULL DEFAULT 'active',
      `tracking_interval` int(11) DEFAULT NULL,
      `idle_timeout` int(11) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`)
    );

    -- Activity Log Table
    CREATE TABLE `activity_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `application_name` varchar(255) NOT NULL,
      `window_title` varchar(255) DEFAULT NULL,
      `is_idle` tinyint(1) NOT NULL DEFAULT 0,
      `tracked_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `fk_user_id` (`user_id`),
      CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    );

    -- Settings Table
    CREATE TABLE `settings` (
      `setting_key` varchar(50) NOT NULL,
      `setting_value` varchar(255) NOT NULL,
      PRIMARY KEY (`setting_key`)
    );

    -- Default Settings
    INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('idle_timeout_seconds', '300'),
    ('tracking_interval_seconds', '15');
    ```

4.  **Configuration**:
    - Open `config/database.php` and ensure the database credentials (`$db_user`, `$db_pass`, `$db_name`) match your setup.

5.  **Access the Application**: Navigate to `http://localhost/FocusFlow/public/login.php` in your web browser.
