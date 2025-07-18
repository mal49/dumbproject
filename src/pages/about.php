<?php require_once '../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Course Registration System</title>
    <!-- CSS modules for about page -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/utilities.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Course Registration System</h1>
        </div>
    </header>

    <?php if (isset($_SESSION['user_id'])): ?>
        <nav>
            <div class="container">
                <ul>
                    <?php if ($_SESSION['user_type'] === 'student'): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="register_course.php">Register Course</a></li>
                        <li><a href="my_courses.php">My Courses</a></li>
                    <?php else: ?>
                        <li><a href="lecturer_dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <div class="container">
        <div class="card">
            <h2>About Course Registration System</h2>
            <p>Welcome to our comprehensive Course Registration System designed for educational institutions to manage
                student course enrollment and drop requests efficiently.</p>
        </div>

        <div class="card">
            <h3>System Features</h3>
            <h4>For Students:</h4>
            <ul>
                <li><strong>User Registration:</strong> Students can create accounts with complete profile information
                </li>
                <li><strong>Course Registration:</strong> Browse and register for available courses</li>
                <li><strong>Course Management:</strong> View registered courses and track enrollment status</li>
                <li><strong>Drop Requests:</strong> Submit course drop requests with reasons to assigned lecturers</li>
                <li><strong>Request Tracking:</strong> Monitor the status of drop requests</li>
            </ul>

            <h4>For Lecturers:</h4>
            <ul>
                <li><strong>Request Management:</strong> View all course drop requests assigned to them</li>
                <li><strong>Decision Making:</strong> Approve or reject student drop requests</li>
                <li><strong>Student Information:</strong> Access student details for informed decision making</li>
                <li><strong>Course Overview:</strong> View all available courses in the system</li>
            </ul>
        </div>

        <div class="card">
            <h3>How to Use the System</h3>

            <h4>For Students:</h4>
            <ol>
                <li><strong>Registration:</strong> Click "Sign Up" on the login page and fill in your details</li>
                <li><strong>Login:</strong> Use your Student ID and password to log in</li>
                <li><strong>Register Courses:</strong> Go to "Register Course" to browse and enroll in courses</li>
                <li><strong>Manage Courses:</strong> Use "My Courses" to view enrolled courses</li>
                <li><strong>Drop Courses:</strong> Request to drop courses by providing a reason and selecting a
                    lecturer</li>
            </ol>

            <h4>For Lecturers:</h4>
            <ol>
                <li><strong>Login:</strong> Use your Lecturer ID with password "lecturer123" (demo)</li>
                <li><strong>Review Requests:</strong> Check the dashboard for pending drop requests</li>
                <li><strong>Make Decisions:</strong> Approve or reject requests based on the provided information</li>
            </ol>
        </div>

        <div class="card">
            <h3>Available Courses</h3>
            <p>Our system currently offers courses in Computer Science and Mathematics:</p>

            <?php
            // Get all available courses
            $stmt = $pdo->query("SELECT * FROM course ORDER BY course_code");
            $courses = $stmt->fetchAll();
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credit Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['credit_hour']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Sample Login Credentials</h3>
            <p>For testing purposes, you can use these sample credentials:</p>

            <h4>Student Accounts:</h4>
            <ul>
                <li><strong>Student ID:</strong> ST001, <strong>Password:</strong> password123</li>
                <li><strong>Student ID:</strong> ST002, <strong>Password:</strong> password123</li>
                <li><strong>Student ID:</strong> ST003, <strong>Password:</strong> password123</li>
            </ul>

            <h4>Lecturer Accounts:</h4>
            <ul>
                <li><strong>Lecturer ID:</strong> LEC01, <strong>Password:</strong> lecturer123</li>
                <li><strong>Lecturer ID:</strong> LEC02, <strong>Password:</strong> lecturer123</li>
                <li><strong>Lecturer ID:</strong> LEC03, <strong>Password:</strong> lecturer123</li>
                <li><strong>Lecturer ID:</strong> LEC04, <strong>Password:</strong> lecturer123</li>
            </ul>
        </div>

        <div class="card">
            <h3>🍪 Lab 9 - Cookie Features</h3>
            <p>This system now includes advanced cookie functionality for Lab 9 PHP exercise:</p>
            <ul>
                <li><strong>Remember Me:</strong> Stay logged in for up to 30 days</li>
                <li><strong>Auto-Login:</strong> Automatic login when returning to the site</li>
                <li><strong>Cookie Security:</strong> HTTPOnly flags for enhanced security</li>
                <li><strong>Cookie Management:</strong> Proper cookie clearing on logout</li>
                <li><strong>Cookie Demonstration:</strong> Interactive cookie testing and viewing</li>
            </ul>

            <div style="text-align: center; margin: 20px 0;">
                <a href="../examples/cookie_demo.php" class="btn"
                    style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    🍪 View Cookie Demonstration
                </a>
            </div>
        </div>

        <div class="card">
            <h3>Technical Information</h3>
            <p>This system is built using:</p>
            <ul>
                <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                <li><strong>Backend:</strong> PHP</li>
                <li><strong>Database:</strong> MySQL (MariaDB)</li>
                <li><strong>Server:</strong> XAMPP Local Development Environment</li>
            </ul>

            <p>The system follows a simple MVC-like structure and uses prepared statements for database security.</p>
        </div>

        <div class="card">
            <h3>Contact & Support</h3>
            <p>This is a demonstration system created for educational purposes. For any questions or support:</p>
            <ul>
                <li>Check the system documentation</li>
                <li>Review the sample data and test credentials</li>
                <li>Ensure your XAMPP server and MySQL are running</li>
            </ul>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="text-center mt-20">
                <a href="../auth/index.php" class="btn">Go to Login Page</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>