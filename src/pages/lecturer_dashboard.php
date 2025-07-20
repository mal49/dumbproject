<?php
require_once '../config/config.php';

// Security: Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'lecturer') {
    header('Location: ../auth/index.php');
    exit;
}

// Initialize variables
$success_message = '';
$error_message = '';
$info_message = '';

// Function to validate CSRF token
function validateCSRF($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to sanitize input
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Handle student deletion with enhanced security
if (isset($_POST['delete_student']) && isset($_POST['csrf_token'])) {
    if (!validateCSRF($_POST['csrf_token'])) {
        $error_message = "Security validation failed. Please try again.";
    } else {
        $student_id = sanitizeInput($_POST['student_id']);

        if (empty($student_id)) {
            $error_message = "Invalid student ID provided.";
        } else {
            try {
                // Check if student exists first
                $check_stmt = $pdo->prepare("SELECT Name FROM student WHERE Student_id = ?");
                $check_stmt->execute([$student_id]);
                $student = $check_stmt->fetch();

                if (!$student) {
                    $error_message = "Student not found.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM student WHERE Student_id = ?");
                    $stmt->execute([$student_id]);
                    $success_message = "Student '" . $student['Name'] . "' deleted successfully!";
                }
            } catch (PDOException $e) {
                $error_message = "Error deleting student: Database operation failed.";
                error_log("Student deletion error: " . $e->getMessage());
            }
        }
    }
}

// Debug: Check if we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $info_message = "POST request detected. Action: " . ($_POST['action_request'] ?? 'NOT SET') . ", Drop ID: " . ($_POST['drop_id'] ?? 'NOT SET');
}

// Handle drop request approval/rejection with enhanced security
if (isset($_POST['action_request']) && isset($_POST['csrf_token'])) {
    $info_message = "Processing approval/rejection request...";

    if (!validateCSRF($_POST['csrf_token'])) {
        $error_message = "Security validation failed. Please try again.";
    } else {
        $drop_id = sanitizeInput($_POST['drop_id']);
        $action = sanitizeInput($_POST['action_request']);

        if (empty($drop_id) || !in_array($action, ['approve', 'reject'])) {
            $error_message = "Invalid request parameters.";
        } else {
            try {
                // Get request details first
                $details_stmt = $pdo->prepare("
                    SELECT cd.course_code, s.Name as student_name 
                    FROM course_drop cd 
                    JOIN add_drop_application ada ON cd.application_id = ada.application_id 
                    JOIN student s ON ada.student_id = s.Student_id 
                    WHERE cd.drop_id = ? AND cd.lecturer_id = ?
                ");
                $details_stmt->execute([$drop_id, $_SESSION['user_id']]);
                $request_details = $details_stmt->fetch();

                if (!$request_details) {
                    $error_message = "Request not found or you don't have permission to process it.";
                } else {
                    if ($action === 'approve') {
                        try {
                            // Start transaction for data consistency
                            $pdo->beginTransaction();

                            // Get student_id from the drop request
                            $student_stmt = $pdo->prepare("
                                SELECT ada.student_id 
                                FROM course_drop cd 
                                JOIN add_drop_application ada ON cd.application_id = ada.application_id 
                                WHERE cd.drop_id = ?
                            ");
                            $student_stmt->execute([$drop_id]);
                            $student_info = $student_stmt->fetch();

                            if (!$student_info) {
                                throw new Exception("Could not find student information for this drop request.");
                            }

                            // Debug: Check if student has enrollment for this course
                            $check_enrollment_stmt = $pdo->prepare("
                                SELECT ca.add_id, ca.application_id 
                                FROM course_add ca
                                JOIN add_drop_application ada ON ca.application_id = ada.application_id
                                WHERE ada.student_id = ? AND ca.course_code = ?
                            ");
                            $check_enrollment_stmt->execute([$student_info['student_id'], $request_details['course_code']]);
                            $enrollment_records = $check_enrollment_stmt->fetchAll();

                            if (empty($enrollment_records)) {
                                throw new Exception("Student is not currently enrolled in course " . $request_details['course_code']);
                            }

                            // Remove the student's enrollment for this course
                            $remove_enrollment_stmt = $pdo->prepare("
                                DELETE ca FROM course_add ca
                                JOIN add_drop_application ada ON ca.application_id = ada.application_id
                                WHERE ada.student_id = ? AND ca.course_code = ?
                            ");
                            $remove_enrollment_stmt->execute([$student_info['student_id'], $request_details['course_code']]);

                            $deleted_rows = $remove_enrollment_stmt->rowCount();

                            if ($deleted_rows === 0) {
                                throw new Exception("Failed to remove student enrollment from course_add table.");
                            }

                            // Remove the drop request
                            $remove_request_stmt = $pdo->prepare("DELETE FROM course_drop WHERE drop_id = ? AND lecturer_id = ?");
                            $remove_request_stmt->execute([$drop_id, $_SESSION['user_id']]);

                            $request_deleted = $remove_request_stmt->rowCount();

                            if ($request_deleted === 0) {
                                throw new Exception("Failed to remove drop request from course_drop table.");
                            }

                            // Commit transaction
                            $pdo->commit();

                            $success_message = "Drop request for " . $request_details['student_name'] . " (" . $request_details['course_code'] . ") approved successfully! Student has been removed from the course. (Removed $deleted_rows enrollment record(s))";

                        } catch (Exception $e) {
                            // Rollback transaction on error
                            $pdo->rollback();
                            $error_message = "Error approving drop request: " . $e->getMessage();
                        }
                    } elseif ($action === 'reject') {
                        // For rejection, just remove the drop request (student stays enrolled)
                        $stmt = $pdo->prepare("DELETE FROM course_drop WHERE drop_id = ? AND lecturer_id = ?");
                        $stmt->execute([$drop_id, $_SESSION['user_id']]);

                        $deleted = $stmt->rowCount();
                        if ($deleted > 0) {
                            $success_message = "Drop request for " . $request_details['student_name'] . " (" . $request_details['course_code'] . ") rejected successfully! Student remains enrolled in the course.";
                        } else {
                            $error_message = "Error: Could not remove the drop request. It may have already been processed.";
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error processing request: Database operation failed.";
                error_log("Drop request processing error: " . $e->getMessage());
            }
        }
    }
}

// Get search parameters
$student_id_search = isset($_GET['student_id']) ? sanitizeInput($_GET['student_id']) : '';
$grouped_student_search = isset($_GET['grouped_student_id']) ? sanitizeInput($_GET['grouped_student_id']) : '';
$semester_filter = isset($_GET['semester_filter']) ? sanitizeInput($_GET['semester_filter']) : '';

// Function to get drop requests
function getDropRequests($pdo, $lecturer_id, $student_search = '')
{
    $sql = "
        SELECT 
            cd.drop_id,
            cd.course_code, 
            c.course_name, 
            cd.Reasons, 
            s.Student_id,
            s.Name as student_name,
            s.Programme_code,
            s.Semester,
            p.Programme_name,
            ada.application_date
        FROM course_drop cd
        JOIN add_drop_application ada ON cd.application_id = ada.application_id 
        JOIN course c ON cd.course_code = c.course_code
        JOIN student s ON ada.student_id = s.Student_id
        JOIN programme p ON s.Programme_code = p.Programme_code
        WHERE cd.lecturer_id = ?
    ";

    $params = [$lecturer_id];
    if (!empty($student_search)) {
        $sql .= " AND s.Student_id LIKE ?";
        $params[] = '%' . $student_search . '%';
    }

    $sql .= " ORDER BY ada.application_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Function to get available semesters
function getAvailableSemesters($pdo)
{
    $sql = "SELECT DISTINCT Semester FROM student ORDER BY Semester";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get students grouped by semester and programme
function getGroupedStudents($pdo, $student_search = '', $semester_filter = '')
{
    $sql = "
        SELECT 
            s.Student_id,
            s.Name as student_name,
            s.Programme_code,
            s.Semester,
            p.Programme_name
        FROM student s
        JOIN programme p ON s.Programme_code = p.Programme_code
    ";

    $params = [];
    $whereConditions = [];

    if (!empty($student_search)) {
        $whereConditions[] = "s.Student_id LIKE ?";
        $params[] = '%' . $student_search . '%';
    }

    if (!empty($semester_filter)) {
        $whereConditions[] = "s.Semester = ?";
        $params[] = $semester_filter;
    }

    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $sql .= " ORDER BY s.Semester, p.Programme_name, s.Student_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Group students by semester and programme
    $grouped = [];
    foreach ($students as $student) {
        $semester = $student['Semester'];
        $programme = $student['Programme_name'];

        if (!isset($grouped[$semester])) {
            $grouped[$semester] = [];
        }

        if (!isset($grouped[$semester][$programme])) {
            $grouped[$semester][$programme] = [];
        }

        $grouped[$semester][$programme][] = $student;
    }

    return $grouped;
}

// Function to get statistics
function getStatistics($pdo, $lecturer_id)
{
    $stats = [];

    // Total drop requests
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_requests FROM course_drop WHERE lecturer_id = ?");
    $stmt->execute([$lecturer_id]);
    $result = $stmt->fetch();
    $stats['total_requests'] = $result['total_requests'] ?? 0;

    // Recent requests (last 7 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as recent_requests 
        FROM course_drop cd 
        JOIN add_drop_application ada ON cd.application_id = ada.application_id 
        WHERE cd.lecturer_id = ? AND ada.application_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$lecturer_id]);
    $result = $stmt->fetch();
    $stats['recent_requests'] = $result['recent_requests'] ?? 0;

    // Total students in system
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_students FROM student");
    $stmt->execute();
    $result = $stmt->fetch();
    $stats['total_students'] = $result['total_students'] ?? 0;

    return $stats;
}

// Get data for display
$drop_requests = getDropRequests($pdo, $_SESSION['user_id'], $student_id_search);
// Get students based on filter - show all if no specific semester selected via dropdown
$show_students = isset($_GET['semester_filter']) || !empty($grouped_student_search);
$students_grouped = $show_students ? getGroupedStudents($pdo, $grouped_student_search, $semester_filter) : [];
$available_semesters = getAvailableSemesters($pdo);
$statistics = getStatistics($pdo, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Course Registration System</title>
    <!-- CSS modules for lecturer dashboard -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/lecturer-dashboard.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/utilities.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <style>
        /* Custom Dropdown Styles */
        .custom-dropdown {
            position: relative;
            display: inline-block;
        }
        
        /* Filter controls styles */
        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 15px 0;
        }

        .filter-controls .btn {
            background-color: #555555;
            border-color: #555555;
            color: white;
            border-radius: 20px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .filter-controls .btn:hover {
            background-color: #444444;
            border-color: #444444;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .dropdown-toggle {
            position: relative;
            min-width: 180px;
            text-align: left;
            padding-right: 35px !important;
        }

        .dropdown-toggle .fa-chevron-down {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }

        .dropdown-toggle.active .fa-chevron-down {
            transform: translateY(-50%) rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-menu.show {
            display: block;
            animation: dropdownFadeIn 0.2s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item.active {
            background-color: #007bff;
            color: white;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dropdown-toggle {
                min-width: 140px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body class="lecturer-dashboard">
    <!-- Header and navigation -->
    <header>
        <div class="container">
            <h1><i class="fas fa-university"></i> Course Registration System - Lecturer Portal</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="lecturer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome card with enhanced styling -->
        <div class="dashboard-card welcome-card">
            <div class="card-body">
                <h2><i class="fas fa-user-tie"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                </h2>
                <div class="lecturer-info">
                    <div class="info-item">
                        <strong><i class="fas fa-id-badge"></i> Lecturer ID:</strong>
                        <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-clock"></i> Login Time:</strong>
                        <?php echo date('Y-m-d H:i:s'); ?>
                    </div>
                </div>



                <p><i class="fas fa-tasks"></i> Manage student course drop requests and view student information from
                    this dashboard.</p>
            </div>
        </div>

        <!-- Alert messages with enhanced styling -->
        <?php if (!empty($success_message)): ?>
            <div class="alert-enhanced alert-success-enhanced">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert-enhanced alert-danger-enhanced">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($info_message)): ?>
            <div class="alert-enhanced" style="background-color: #e3f2fd; color: #1976d2; border: 1px solid #2196f3;">
                <i class="fas fa-info-circle"></i>
                <?php echo $info_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar icon"></i> Dashboard Statistics</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $statistics['total_requests']; ?></div>
                        <div class="stat-label"><i class="fas fa-clipboard-list"></i> Total Drop Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $statistics['recent_requests']; ?></div>
                        <div class="stat-label"><i class="fas fa-calendar-week"></i> Recent Requests (7 days)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $statistics['total_students']; ?></div>
                        <div class="stat-label"><i class="fas fa-users"></i> Total Students</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Drop Requests section with enhanced styling -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user-minus icon"></i> Course Drop Requests</h3>
            </div>
            <div class="card-body">
                <!-- Enhanced Search Form -->
                <div class="search-section">
                    <form method="GET" action="">
                        <div class="search-container-enhanced">
                            <div class="search-field">
                                <label for="student_id_search"><i class="fas fa-search"></i> Search by Student
                                    ID</label>
                                <input type="text" id="student_id_search" name="student_id" class="search-input"
                                    placeholder="Enter student ID to search..."
                                    value="<?php echo htmlspecialchars($student_id_search); ?>">
                            </div>
                            <div class="search-actions filter-controls">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <?php if (!empty($student_id_search)): ?>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='lecturer_dashboard.php'">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (empty($drop_requests)): ?>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p style="font-size: 1.1rem; margin: 0;">
                            <?php echo !empty($student_id_search) ? "No drop requests found for the search criteria." : "No pending drop requests assigned to you."; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="enhanced-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-card"></i> Student ID</th>
                                    <th><i class="fas fa-user"></i> Student Name</th>
                                    <th><i class="fas fa-graduation-cap"></i> Programme</th>
                                    <th><i class="fas fa-calendar"></i> Semester</th>
                                    <th><i class="fas fa-book"></i> Course Code</th>
                                    <th><i class="fas fa-book-open"></i> Course Name</th>
                                    <th><i class="fas fa-comment"></i> Reason</th>
                                    <th><i class="fas fa-clock"></i> Request Date</th>
                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drop_requests as $request): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($request['Student_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['Programme_name']); ?></td>
                                        <td><span class="badge"><?php echo htmlspecialchars($request['Semester']); ?></span>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($request['course_code']); ?></code></td>
                                        <td><?php echo htmlspecialchars($request['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['Reasons']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($request['application_date'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <form method="POST" style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to approve this drop request for <?php echo htmlspecialchars($request['student_name']); ?>?');">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="drop_id"
                                                        value="<?php echo $request['drop_id']; ?>">
                                                    <input type="hidden" name="action_request" value="approve">
                                                    <button type="submit" class="btn-enhanced btn-approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to reject this drop request for <?php echo htmlspecialchars($request['student_name']); ?>?');">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="drop_id"
                                                        value="<?php echo $request['drop_id']; ?>">
                                                    <input type="hidden" name="action_request" value="reject">
                                                    <button type="submit" class="btn-enhanced btn-reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Students by Semester and Programme Section with enhanced styling -->
        <div class="dashboard-card" id="students-section">
            <div class="card-header">
                <h3><i class="fas fa-users icon"></i> Students by Semester and Programme</h3>
            </div>
            <div class="card-body">
                <!-- Enhanced Search Form for Grouped Students -->
                <div class="search-section">
                    <form method="GET" action="" id="studentFilterForm">
                        <div class="search-container-enhanced">
                            <div class="search-field">
                                <label for="grouped_student_search"><i class="fas fa-search"></i> Search Student by
                                    ID</label>
                                <input type="text" id="grouped_student_search" name="grouped_student_id"
                                    class="search-input" placeholder="Enter student ID to search..."
                                    value="<?php echo htmlspecialchars($grouped_student_search); ?>">
                            </div>
                            <div class="search-actions filter-controls">
                                <!-- Custom Dropdown Button -->
                                <div class="custom-dropdown">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" id="semesterDropdownBtn">
                                        <i class="fas fa-filter"></i>
                                        <?php if (!empty($semester_filter)): ?>
                                            Semester <?php echo htmlspecialchars($semester_filter); ?>
                                        <?php elseif (isset($_GET['semester_filter'])): ?>
                                            All Semesters
                                        <?php else: ?>
                                            View Students
                                        <?php endif; ?>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu" id="semesterDropdownMenu">
                                        <div class="dropdown-item <?php echo (isset($_GET['semester_filter']) && empty($semester_filter)) ? 'active' : ''; ?>"
                                            data-value="">
                                            <i class="fas fa-list"></i> All Semesters
                                        </div>
                                        <?php foreach ($available_semesters as $semester): ?>
                                            <div class="dropdown-item <?php echo ($semester_filter == $semester) ? 'active' : ''; ?>"
                                                data-value="<?php echo htmlspecialchars($semester); ?>">
                                                <i class="fas fa-calendar-alt"></i> Semester
                                                <?php echo htmlspecialchars($semester); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Hidden form elements -->
                                <input type="hidden" id="semester_filter" name="semester_filter"
                                    value="<?php echo htmlspecialchars($semester_filter); ?>">

                                <?php if (!empty($grouped_student_search)): ?>
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (!empty($grouped_student_search) || isset($_GET['semester_filter'])): ?>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='lecturer_dashboard.php'">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (empty($students_grouped)): ?>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <?php if (!$show_students): ?>
                            <i class="fas fa-calendar-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p style="font-size: 1.1rem; margin: 0;">
                                Please select a semester from the dropdown above to view students.
                            </p>
                            <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.7;">
                                Use the semester filter to display students organized by programme.
                            </p>
                        <?php else: ?>
                            <i class="fas fa-user-slash" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p style="font-size: 1.1rem; margin: 0;">
                                <?php
                                if (!empty($grouped_student_search) && !empty($semester_filter)) {
                                    echo "No students found for the search criteria in Semester " . htmlspecialchars($semester_filter) . ".";
                                } elseif (!empty($grouped_student_search)) {
                                    echo "No students found for the search criteria.";
                                } elseif (!empty($semester_filter)) {
                                    echo "No students found in Semester " . htmlspecialchars($semester_filter) . ".";
                                } else {
                                    echo "No students found in the system.";
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($students_grouped as $semester => $programmes): ?>
                        <div class="semester-section">
                            <div class="semester-header-enhanced">
                                <i class="fas fa-calendar-alt"></i>
                                Semester <?php echo htmlspecialchars($semester); ?>
                            </div>

                            <?php foreach ($programmes as $programme_name => $programme_students): ?>
                                <div class="programme-section">
                                    <div class="programme-header-enhanced">
                                        <i class="fas fa-graduation-cap"></i>
                                        Programme: <?php echo htmlspecialchars($programme_name); ?>
                                        <span style="margin-left: auto; font-size: 0.9rem; opacity: 0.7;">
                                            (<?php echo count($programme_students); ?> students)
                                        </span>
                                    </div>
                                    <div class="programme-content">
                                        <div style="overflow-x: auto;">
                                            <table class="enhanced-table">
                                                <thead>
                                                    <tr>
                                                        <th><i class="fas fa-id-card"></i> Student ID</th>
                                                        <th><i class="fas fa-user"></i> Name</th>
                                                        <th><i class="fas fa-calendar"></i> Semester</th>
                                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($programme_students as $student): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($student['Student_id']); ?></strong>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                                            <td><span
                                                                    class="badge"><?php echo htmlspecialchars($student['Semester']); ?></span>
                                                            </td>
                                                            <td>
                                                                <form method="POST" style="display: inline;"
                                                                    onsubmit="return confirm('Are you sure you want to delete student <?php echo htmlspecialchars($student['student_name']); ?>? This action cannot be undone.');">
                                                                    <input type="hidden" name="csrf_token"
                                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                    <input type="hidden" name="student_id"
                                                                        value="<?php echo htmlspecialchars($student['Student_id']); ?>">
                                                                    <button type="submit" name="delete_student"
                                                                        class="btn-enhanced btn-delete">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Loading overlay (hidden by default) -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>

    <script>
        // Enhanced form submission with loading states
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('form[method="POST"]');
            const loadingOverlay = document.getElementById('loadingOverlay');

            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    const button = form.querySelector('button[type="submit"]');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                        // Show loading overlay for delete operations
                        if (button.name === 'delete_student' || button.name === 'action_request') {
                            loadingOverlay.style.display = 'flex';
                        }
                    }
                });
            });

            // Handle filter form to maintain scroll position
            const filterForm = document.getElementById('studentFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    // Add anchor to maintain position
                    const action = filterForm.getAttribute('action') || '';
                    filterForm.setAttribute('action', action + '#students-section');
                });
            }

            // Handle custom dropdown functionality
            const dropdownBtn = document.getElementById('semesterDropdownBtn');
            const dropdownMenu = document.getElementById('semesterDropdownMenu');
            const semesterFilterInput = document.getElementById('semester_filter');

            if (dropdownBtn && dropdownMenu && filterForm) {
                // Toggle dropdown on button click
                dropdownBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                    dropdownBtn.classList.toggle('active');
                });

                // Handle dropdown item clicks
                const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
                dropdownItems.forEach((item, index) => {
                    item.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const value = this.getAttribute('data-value');

                        // Update hidden input
                        if (semesterFilterInput) {
                            semesterFilterInput.value = value;
                        }

                        // Update button text
                        const icon = '<i class="fas fa-filter"></i> ';
                        const chevron = ' <i class="fas fa-chevron-down"></i>';
                        if (value) {
                            dropdownBtn.innerHTML = icon + 'Semester ' + value + chevron;
                        } else {
                            dropdownBtn.innerHTML = icon + 'All Semesters' + chevron;
                        }

                        // Update active state
                        dropdownItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');

                        // Close dropdown
                        dropdownMenu.classList.remove('show');
                        dropdownBtn.classList.remove('active');

                        // Add anchor to maintain position and submit form
                        const action = filterForm.getAttribute('action') || '';
                        const cleanAction = action.split('#')[0];
                        filterForm.setAttribute('action', cleanAction + '#students-section');
                        filterForm.submit();
                    });
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        if (dropdownMenu.classList.contains('show')) {
                            dropdownMenu.classList.remove('show');
                            dropdownBtn.classList.remove('active');
                        }
                    }
                });

                // Close dropdown on escape key
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                        dropdownBtn.classList.remove('active');
                    }
                });
            }

            // Handle clear filters button to maintain scroll position
            const clearButton = document.querySelector('.btn-clear');
            if (clearButton) {
                clearButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Navigate to clear page with anchor
                    window.location.href = 'lecturer_dashboard.php#students-section';
                });
            }

            // Auto-scroll to students section if filters are applied or coming from clear
            const urlParams = new URLSearchParams(window.location.search);
            const hasFilters = urlParams.get('semester_filter') || urlParams.get('grouped_student_id');
            const hasAnchor = window.location.hash === '#students-section';

            if (hasFilters || hasAnchor) {
                setTimeout(() => {
                    const studentsSection = document.querySelector('#students-section');
                    if (studentsSection) {
                        studentsSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 100);
            }

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-enhanced');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>

</html>