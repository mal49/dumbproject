<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: index.php');
    exit;
}

// Handle course registration
if (isset($_POST['register_course'])) {
    $course_code = $_POST['course_code'];
    $is_repeat = isset($_POST['is_repeat']) ? 1 : 0;

    try {
        // Create new application
        $application_id = 'APP' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO add_drop_application (application_id, student_id, application_date) VALUES (?, ?, CURDATE())");
        $stmt->execute([$application_id, $_SESSION['user_id']]);

        // Add course to application
        $add_id = 'ADD' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO course_add (add_id, application_id, course_code, is_repeat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$add_id, $application_id, $course_code, $is_repeat]);

        $success_message = "Course registered successfully!";
    } catch (PDOException $e) {
        $error_message = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Course - Course Registration System</title>
    <link rel="stylesheet" href="styles.css">
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Register for Courses</h2>
            <p>Browse available courses and register for the ones you want to take.</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Available Courses</h3>

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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['credit_hour']); ?></td>
                            <td>
                                <button
                                    onclick="registerCourse('<?php echo $course['course_code']; ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')"
                                    class="btn btn-success">Register</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Registration Modal -->
        <div id="registrationModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 400px; width: 90%;">
                <h3>Register for Course</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Course Code:</label>
                        <input type="text" id="modalCourseCode" name="course_code" readonly
                            style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label>Course Name:</label>
                        <input type="text" id="modalCourseName" readonly style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_repeat">
                            This is a repeat course
                        </label>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" name="register_course" class="btn btn-success">Confirm
                            Registration</button>
                        <button type="button" onclick="closeModal()" class="btn"
                            style="margin-left: 10px;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function registerCourse(courseCode, courseName) {
            document.getElementById('modalCourseCode').value = courseCode;
            document.getElementById('modalCourseName').value = courseName;
            document.getElementById('registrationModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('registrationModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('registrationModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>