<?php
require_once '../config/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../auth/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Course Registration System</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Course Registration System</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="register_course.php">Register Course</a></li>
                <li><a href="my_courses.php">My Courses</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            <p>Student ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>

            <?php if (isset($_COOKIE['remember_user_id'])): ?>
                <div
                    style="background-color: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #4CAF50;">
                    <p style="margin: 0; color: #2e7d32;">
                        <strong>🔐 Auto-Login:</strong> You were automatically logged in using your "Remember Me"
                        preferences!
                        Your login details are securely stored for 30 days.
                    </p>
                </div>
            <?php endif; ?>

            <p>Welcome to the Course Registration System. Use the navigation menu to access different features.</p>
        </div>

        <div class="dashboard-menu">
            <div class="menu-item">
                <a href="register_course.php">
                    <h3>📚 Register Course</h3>
                    <p>Browse and register for available courses</p>
                </a>
            </div>

            <div class="menu-item">
                <a href="my_courses.php">
                    <h3>📋 My Courses</h3>
                    <p>View registered courses and drop requests</p>
                </a>
            </div>

            <div class="menu-item">
                <a href="about.php">
                    <h3>ℹ️ About</h3>
                    <p>Learn more about this system</p>
                </a>
            </div>
        </div>

        <div class="card mt-20">
            <h3>Quick Stats</h3>
            <?php
            // Get student's current registered courses count
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT ca.course_code) as course_count 
                FROM add_drop_application ada 
                JOIN course_add ca ON ada.application_id = ca.application_id 
                WHERE ada.student_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $stats = $stmt->fetch();

            // Get pending drop requests
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as pending_drops 
                FROM add_drop_application ada 
                JOIN course_drop cd ON ada.application_id = cd.application_id 
                WHERE ada.student_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $pending = $stmt->fetch();
            ?>

            <p><strong>Registered Courses:</strong> <?php echo $stats['course_count'] ?? 0; ?></p>
            <p><strong>Pending Drop Requests:</strong> <?php echo $pending['pending_drops'] ?? 0; ?></p>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>