<?php
require_once '../config/config.php';

// Use simplified authentication check
AuthManager::requireAuth('student');

// Handle course drop request
if (isset($_POST['drop_course'])) {
    $validator = new FormValidator();

    // Validate input
    if ($validator->validateDropRequest($_POST)) {
        try {
            // Use simplified database methods
            $applicationId = $dbManager->createApplication($_SESSION['user_id']);
            $dbManager->addDropRequest($applicationId, $_POST['course_code'], $_POST['reason'], $_POST['lecturer_id']);

            $success_message = "Drop request submitted successfully! Waiting for lecturer approval.";
        } catch (PDOException $e) {
            $error_message = "Drop request failed: " . $e->getMessage();
        }
    } else {
        $error_message = "Validation failed: " . implode(', ', $validator->getErrorMessages());
    }
}
// Set page configuration
$pageTitle = 'My Courses - Course Registration System';
$cssFiles = ['dashboard', 'forms', 'components', 'utilities'];

// Include header template
include '../includes/header.php';

// Include navigation template
include '../includes/navigation.php';
?>

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
        // Use simplified database method
        $registered_courses = $dbManager->getStudentRegisteredCourses($_SESSION['user_id']);
        ?>

        <?php if (empty($registered_courses)): ?>
            <p style="margin-bottom: 5px;">You haven't registered for any courses yet.</p>
            <a href="register_course.php" class="btn">Register for Courses</a>
        <?php else: ?>
            <table>
                <thead style="color: white;">
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
                        // Use simplified database query
                        $lecturers = $pdo->query("SELECT lecturer_id, lecturer_name FROM lecturer ORDER BY lecturer_name")->fetchAll();
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

<?php include '../includes/footer.php'; ?>