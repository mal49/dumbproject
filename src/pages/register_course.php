<?php
require_once '../config/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php');
    exit;
}

// Handle course registration
if (isset($_POST['register_course'])) {
    $course_code = $_POST['course_code'];
    $is_repeat = isset($_POST['is_repeat']) ? 1 : 0;

    try {
        // Check pre-requisites using the correct column name
        $stmt = $pdo->prepare("
            SELECT cp.preequisite as prerequisite_code, c.course_name 
            FROM course_prerequisite cp
            JOIN course c ON cp.preequisite = c.course_code
            WHERE cp.course_code = ?
            AND cp.preequisite NOT IN (
                SELECT course_code FROM passed_courses WHERE student_id = ?
            )
        ");
        $stmt->execute([$course_code, $_SESSION['user_id']]);
        $missing_prerequisite = $stmt->fetchAll();

        if (!empty($missing_prerequisite)) {
            $error_message = "You haven't completed the following pre-requisites:<ul>";
            foreach ($missing_prerequisite as $prereq) {
                $error_message .= "<li>" . htmlspecialchars($prereq['prerequisite_code']) . " - " . htmlspecialchars($prereq['course_name']) . "</li>";
            }
            $error_message .= "</ul>";
            throw new Exception($error_message);
        }

        // Check if already registered for this course in current semester
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM add_drop_application ada
            JOIN course_add ca ON ada.application_id = ca.application_id
            WHERE ada.student_id = ? AND ca.course_code = ?
            AND YEAR(ada.application_date) = YEAR(CURDATE())
            AND (
                (MONTH(ada.application_date) BETWEEN 1 AND 5 AND MONTH(CURDATE()) BETWEEN 1 AND 5) OR
                (MONTH(ada.application_date) BETWEEN 6 AND 12 AND MONTH(CURDATE()) BETWEEN 6 AND 12)
            )
        ");
        $stmt->execute([$_SESSION['user_id'], $course_code]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception("You are already registered for this course in the current semester.");
        }

        // Check current semester credit hours
        $stmt = $pdo->prepare("
            SELECT SUM(c.credit_hour) as total_credits
            FROM add_drop_application ada
            JOIN course_add ca ON ada.application_id = ca.application_id
            JOIN course c ON ca.course_code = c.course_code
            WHERE ada.student_id = ?
            AND YEAR(ada.application_date) = YEAR(CURDATE())
            AND (
                (MONTH(ada.application_date) BETWEEN 1 AND 5 AND MONTH(CURDATE()) BETWEEN 1 AND 5) OR
                (MONTH(ada.application_date) BETWEEN 6 AND 12 AND MONTH(CURDATE()) BETWEEN 6 AND 12)
            )
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $credits = $stmt->fetch();

        $current_credits = $credits['total_credits'] ?? 0;

        // Get new course credit hours
        $stmt = $pdo->prepare("SELECT credit_hour FROM course WHERE course_code = ?");
        $stmt->execute([$course_code]);
        $course = $stmt->fetch();
        $new_credits = $current_credits + $course['credit_hour'];

        if ($new_credits > 21) {
            throw new Exception("Cannot register: Total credit hours ($new_credits) would exceed maximum limit of 21 credits per semester.");
        }

        // Create new application
        $application_id = 'APP' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO add_drop_application (application_id, student_id, application_date) VALUES (?, ?, CURDATE())");
        $stmt->execute([$application_id, $_SESSION['user_id']]);

        // Add course to application
        $add_id = 'ADD' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO course_add (add_id, application_id, course_code, is_repeat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$add_id, $application_id, $course_code, $is_repeat]);

        $success_message = "Course registered successfully!";

        if ($new_credits < 12) {
            $success_message .= "<br><div class='alert alert-warning'>Warning: You're registering for only $new_credits credit hours this semester. The minimum requirement is 12 credit hours.</div>";
        }

    } catch (PDOException $e) {
        $error_message = "Registration failed: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Course - Course Registration System</title>
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

        <!-- Registration modal with pre-requisites -->
        <div id="registrationModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <h3>Register for Course</h3>
                <div id="prerequisiteInfo" style="margin-bottom: 15px;"></div>
                <form method="POST" id="registrationForm">
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
                        <button type="submit" name="register_course" class="btn btn-success" id="confirmButton">Confirm
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

            // Show loading message
            document.getElementById('prerequisiteInfo').innerHTML = '<div class="alert">Loading prerequisites...</div>';
            document.getElementById('registrationModal').style.display = 'block';

            // Fetch pre-requisites for this course
            fetch('get_prerequisite.php?course_code=' + encodeURIComponent(courseCode))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('prerequisiteInfo').innerHTML = data;

                    // Check if there are incomplete prerequisites
                    if (data.includes('Not Completed') || data.includes('must complete all prerequisites')) {
                        document.getElementById('confirmButton').disabled = true;
                        document.getElementById('confirmButton').style.backgroundColor = '#ccc';
                        document.getElementById('confirmButton').style.cursor = 'not-allowed';
                    } else {
                        document.getElementById('confirmButton').disabled = false;
                        document.getElementById('confirmButton').style.backgroundColor = '#28a745';
                        document.getElementById('confirmButton').style.cursor = 'pointer';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('prerequisiteInfo').innerHTML = '<div class="alert alert-danger">Error loading prerequisites. Please try again.</div>';
                });
        }

        function closeModal() {
            document.getElementById('registrationModal').style.display = 'none';
            // Reset form
            document.getElementById('registrationForm').reset();
            document.getElementById('confirmButton').disabled = false;
            document.getElementById('confirmButton').style.backgroundColor = '#28a745';
            document.getElementById('confirmButton').style.cursor = 'pointer';
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