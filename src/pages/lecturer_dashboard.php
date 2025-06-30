<?php
require_once '../config/config.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'lecturer') {
    header('Location: ../auth/index.php');
    exit;
}

// Handle drop request approval/rejection
if (isset($_POST['action_request'])) {
    $drop_id = $_POST['drop_id'];
    $action = $_POST['action_request']; // 'approve' or 'reject'

    if ($action === 'approve') {
        // For demo purposes, we'll just delete the drop request to simulate approval
        try {
            $stmt = $pdo->prepare("DELETE FROM course_drop WHERE drop_id = ?");
            $stmt->execute([$drop_id]);
            $success_message = "Drop request approved successfully!";
        } catch (PDOException $e) {
            $error_message = "Error approving request: " . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        // For demo purposes, we'll also delete the request but show different message
        try {
            $stmt = $pdo->prepare("DELETE FROM course_drop WHERE drop_id = ?");
            $stmt->execute([$drop_id]);
            $success_message = "Drop request rejected successfully!";
        } catch (PDOException $e) {
            $error_message = "Error rejecting request: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Course Registration System</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Course Registration System - Lecturer Portal</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="lecturer_dashboard.php">Dashboard</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            <p>Lecturer ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>

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

            <p>Manage student course drop requests from this dashboard.</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Course Drop Requests Assigned to You</h3>

            <?php
            // Get drop requests assigned to this lecturer
            $stmt = $pdo->prepare("
                SELECT 
                    cd.drop_id,
                    cd.course_code, 
                    c.course_name, 
                    cd.Reasons, 
                    s.Student_id,
                    s.Name as student_name,
                    s.Programme_code,
                    ada.application_date
                FROM course_drop cd
                JOIN add_drop_application ada ON cd.application_id = ada.application_id 
                JOIN course c ON cd.course_code = c.course_code
                JOIN student s ON ada.student_id = s.Student_id
                WHERE cd.lecturer_id = ?
                ORDER BY ada.application_date DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $drop_requests = $stmt->fetchAll();
            ?>

            <?php if (empty($drop_requests)): ?>
                <p>No pending drop requests assigned to you.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Programme</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Reason</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drop_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['Student_id']); ?></td>
                                <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['Programme_code']); ?></td>
                                <td><?php echo htmlspecialchars($request['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($request['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['Reasons']); ?></td>
                                <td><?php echo htmlspecialchars($request['application_date']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="drop_id" value="<?php echo $request['drop_id']; ?>">
                                        <button type="submit" name="action_request" value="approve" class="btn btn-success"
                                            style="min-width: 80px; padding: 8px 12px; margin-bottom: 5px;"
                                            onclick="return confirm('Are you sure you want to approve this drop request?')">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline; margin-left: 15px;">
                                        <input type="hidden" name="drop_id" value="<?php echo $request['drop_id']; ?>">
                                        <button type="submit" name="action_request" value="reject" class="btn btn-danger"
                                            style="min-width: 80px; padding: 8px 12px;"
                                            onclick="return confirm('Are you sure you want to reject this drop request?')">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Statistics</h3>
            <?php
            // Get statistics for this lecturer
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_requests 
                FROM course_drop 
                WHERE lecturer_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $stats = $stmt->fetch();
            ?>

            <p><strong>Total Drop Requests Assigned:</strong> <?php echo $stats['total_requests'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>All Available Courses</h3>

            <?php
            // Get all courses for reference
            $stmt = $pdo->query("SELECT * FROM course ORDER BY course_code");
            $all_courses = $stmt->fetchAll();
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
                    <?php foreach ($all_courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['credit_hour']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>