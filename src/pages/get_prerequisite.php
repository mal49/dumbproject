<?php
require_once '../config/config.php';

if (isset($_GET['course_code']) && isset($_SESSION['user_id'])) {
    $course_code = $_GET['course_code'];
    $student_id = $_SESSION['user_id'];

    // Get all pre-requisites for this course
    $stmt = $pdo->prepare("
        SELECT cp.prerequisite as prerequisite_code, c.course_name,
               CASE WHEN pc.course_code IS NOT NULL THEN 'Completed' ELSE 'Not Completed' END as status
        FROM course_prerequisite cp
        JOIN course c ON cp.prerequisite = c.course_code
        LEFT JOIN passed_courses pc ON cp.prerequisite = pc.course_code AND pc.student_id = ?
        WHERE cp.course_code = ?
    ");
    $stmt->execute([$student_id, $course_code]);
    $prerequisite = $stmt->fetchAll();

    if (!empty($prerequisite)) {
        echo '<h4>Pre-requisites:</h4><ul>';
        foreach ($prerequisite as $prereq) {
            $status_class = $prereq['status'] === 'Completed' ? 'text-success' : 'text-danger';
            echo '<li>' . htmlspecialchars($prereq['prerequisite_code']) . ' - ' .
                htmlspecialchars($prereq['course_name']) .
                ' <span class="' . $status_class . '">(' . $prereq['status'] . ')</span></li>';
        }
        echo '</ul>';

        // Check if all prerequisites are completed
        $all_completed = true;
        foreach ($prerequisite as $prereq) {
            if ($prereq['status'] !== 'Completed') {
                $all_completed = false;
                break;
            }
        }

        if (!$all_completed) {
            echo '<div class="alert alert-danger">You must complete all prerequisites before registering for this course.</div>';
        }
    } else {
        echo '<div class="alert alert-success">No pre-requisites required for this course.</div>';
    }

    // Show current semester credit calculation
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
    $stmt->execute([$student_id]);
    $credits = $stmt->fetch();
    $current_credits = $credits['total_credits'] ?? 0;

    // Get this course's credit hours
    $stmt = $pdo->prepare("SELECT credit_hour FROM course WHERE course_code = ?");
    $stmt->execute([$course_code]);
    $course = $stmt->fetch();
    $new_total_credits = $current_credits + $course['credit_hour'];

    echo '<div class="credit-info" style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">';
    echo '<h5>Credit Hour Information:</h5>';
    echo '<p>Current semester credits: <strong>' . $current_credits . '</strong></p>';
    echo '<p>This course credits: <strong>' . $course['credit_hour'] . '</strong></p>';
    echo '<p>Total after registration: <strong>' . $new_total_credits . '</strong></p>';

    if ($new_total_credits < 12) {
        echo '<div class="alert alert-warning">Warning: Total credits (' . $new_total_credits . ') will be below minimum requirement of 12 credit hours.</div>';
    } else {
        echo '<div class="alert alert-success">Credit hours meet the minimum requirement.</div>';
    }
    echo '</div>';
}
?>