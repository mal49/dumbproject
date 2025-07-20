<?php
require_once '../config/config.php';

// Use simplified authentication check
AuthManager::requireAuth('student');

// Set page configuration
$pageTitle = 'Student Dashboard - Course Registration System';
$cssFiles = ['dashboard', 'components', 'utilities'];

// Include header template
include '../includes/header.php';

// Include navigation template
include '../includes/navigation.php';
?>

<div class="container">
    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Student ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>



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

        <div class="menu-item">
            <a href="update_profile.php">
                <h3>👤 Update Profile</h3>
                <p>Update your personal information</p>
            </a>
        </div>
    </div>

    <div class="card mt-20">
        <h3>Quick Stats</h3>
        <?php
        // Use simplified database methods
        $registeredCourses = $dbManager->getStudentRegisteredCourses($_SESSION['user_id']);
        $pendingDrops = $dbManager->getStudentPendingDropRequests($_SESSION['user_id']);
        ?>

        <p><strong>Registered Courses:</strong> <?php echo count($registeredCourses); ?></p>
        <p><strong>Pending Drop Requests:</strong> <?php echo $pendingDrops; ?></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>