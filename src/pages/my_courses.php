<?php
require_once '../config/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../auth/index.php');
    exit;
}

// Handle course drop request
if (isset($_POST['drop_course'])) {
    $course_code = $_POST['course_code'];
    $reason = $_POST['reason'];
    $lecturer_id = $_POST['lecturer_id'];

    try {
        // Create new application for drop
        $application_id = 'APP' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO add_drop_application (application_id, student_id, application_date) VALUES (?, ?, CURDATE())");
        $stmt->execute([$application_id, $_SESSION['user_id']]);

        // Add course drop request
        $drop_id = 'DROP' . time() . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO course_drop (drop_id, application_id, course_code, Reasons, lecturer_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$drop_id, $application_id, $course_code, $reason, $lecturer_id]);

        $success_message = "Drop request submitted successfully! Waiting for lecturer approval.";
    } catch (PDOException $e) {
        $error_message = "Drop request failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Course Registration System</title>
    <!-- CSS modules for my courses page -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/utilities.css">
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
            <h2>My Registered Courses</h2>
            <p>View your registered courses and submit drop requests if needed.</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Currently Registered Courses</h3>

            <?php
            // Get student's registered courses
            $stmt = $pdo->prepare("
                SELECT DISTINCT c.course_code, c.course_name, c.credit_hour, ca.is_repeat
                FROM add_drop_application ada 
                JOIN course_add ca ON ada.application_id = ca.application_id 
                JOIN course c ON ca.course_code = c.course_code
                WHERE ada.student_id = ?
                ORDER BY c.course_code
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $registered_courses = $stmt->fetchAll();
            ?>

            <?php if (empty($registered_courses)): ?>
                <p>You haven't registered for any courses yet.</p>
                <a href="register_course.php" class="btn">Register for Courses</a>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credit Hours</th>
                            <th>Repeat Course</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registered_courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['credit_hour']); ?></td>
                                <td><?php echo $course['is_repeat'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <button
                                        onclick="dropCourse('<?php echo $course['course_code']; ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')"
                                        class="btn btn-danger">Request Drop</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Pending Drop Requests</h3>

            <?php
            // Get student's pending drop requests
            $stmt = $pdo->prepare("
                SELECT cd.course_code, c.course_name, cd.Reasons, cd.lecturer_id, l.lecturer_name, ada.application_date
                FROM add_drop_application ada 
                JOIN course_drop cd ON ada.application_id = cd.application_id 
                JOIN course c ON cd.course_code = c.course_code
                JOIN lecturer l ON cd.lecturer_id = l.lecturer_id
                WHERE ada.student_id = ?
                ORDER BY ada.application_date DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $drop_requests = $stmt->fetchAll();
            ?>

            <?php if (empty($drop_requests)): ?>
                <p>No pending drop requests.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Reason</th>
                            <th>Assigned Lecturer</th>
                            <th>Request Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drop_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($request['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['Reasons']); ?></td>
                                <td><?php echo htmlspecialchars($request['lecturer_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['application_date']); ?></td>
                                <td><span style="color: orange;">Pending</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Drop Course Modal -->
        <div id="dropModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
                <h3>Request Course Drop</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Course Code:</label>
                        <input type="text" id="dropCourseCode" name="course_code" readonly style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label>Course Name:</label>
                        <input type="text" id="dropCourseName" readonly style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label for="lecturer_id">Assign to Lecturer:</label>
                        <select name="lecturer_id" required>
                            <option value="">Select Lecturer</option>
                            <?php
                            $stmt = $pdo->query("SELECT lecturer_id, lecturer_name FROM lecturer ORDER BY lecturer_name");
                            $lecturers = $stmt->fetchAll();
                            foreach ($lecturers as $lecturer):
                                ?>
                                <option value="<?php echo $lecturer['lecturer_id']; ?>">
                                    <?php echo htmlspecialchars($lecturer['lecturer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Drop:</label>
                        <textarea name="reason" rows="4"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                            placeholder="Please provide a reason for dropping this course..." required></textarea>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" name="drop_course" class="btn btn-danger">Submit Drop Request</button>
                        <button type="button" onclick="closeDropModal()" class="btn"
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
        function dropCourse(courseCode, courseName) {
            document.getElementById('dropCourseCode').value = courseCode;
            document.getElementById('dropCourseName').value = courseName;
            document.getElementById('dropModal').style.display = 'block';
        }

        function closeDropModal() {
            document.getElementById('dropModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('dropModal');
            if (event.target === modal) {
                closeDropModal();
            }
        }
    </script>
</body>

</html>