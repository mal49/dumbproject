<?php
require_once '../config/config.php';

// Set page configuration
$pageTitle = 'About - Course Registration System';
$cssFiles = ['dashboard', 'components', 'utilities'];

// Include header template
include '../includes/header.php';
?>
<style>
    .hero-section {
        background: #555555;
        color: white;
        text-align: center;
        padding: 60px 0;
        margin-bottom: 40px;
    }

    .hero-section h1 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        font-weight: 300;
    }

    .hero-section p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 40px 0;
    }

    .feature-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: block;
    }

    .feature-card h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
    }

    .stats-section {
        background: #f8f9fa;
        padding: 40px 0;
        margin: 40px 0;
        border-radius: 12px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        color: #667eea;
        margin-bottom: 10px;
    }

    .stat-item p {
        color: #666;
        font-weight: 500;
    }

    .course-preview {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .course-preview table {
        width: 100%;
        border-collapse: collapse;
    }

    .course-preview th,
    .course-preview td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .course-preview th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
    }

    .cta-section {
        text-align: center;
        padding: 40px 0;
    }

    .cta-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .cta-button:hover {
        transform: translateY(-2px);
        color: white;
    }
    </style>

<?php 
// Include navigation template
include '../includes/navigation.php';
?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1>Streamlined Course Registration</h1>
            <p>A modern, secure, and intuitive platform designed to simplify academic enrollment for students and
                faculty alike.</p>
        </div>
    </div>

    <div class="container">
        <!-- Features Grid -->
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🎓</span>
                <h3>For Students</h3>
                <p>Browse courses, register instantly, manage your schedule, and track your academic progress with ease.
                </p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">👨‍🏫</span>
                <h3>For Faculty</h3>
                <p>Manage enrollments, review student requests, and access comprehensive course administration tools.
                </p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <h3>Secure & Reliable</h3>
                <p>Bank-level security with encrypted data transmission and 99.9% uptime guarantee.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📱</span>
                <h3>Mobile Ready</h3>
                <p>Fully responsive design that works seamlessly across all devices and screen sizes.</p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <?php
                    // Use simplified database method
                    $course_count = $dbManager->getCourseCount();
                    ?>
                    <div class="stat-item">
                        <h3><?php echo $course_count; ?>+</h3>
                        <p>Available Courses</p>
                    </div>
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>System Availability</p>
                    </div>
                    <div class="stat-item">
                        <h3>99.9%</h3>
                        <p>Uptime Guarantee</p>
                    </div>
                    <div class="stat-item">
                        <h3>Instant</h3>
                        <p>Registration Confirmation</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Preview -->
        <div class="course-preview">
            <h3 style="text-align: center; margin-bottom: 30px; color: #333;">Available Courses</h3>
            <?php
            // Use simplified database method and limit to 5 for preview
            $allCourses = $dbManager->getAllCourses();
            $courses = array_slice($allCourses, 0, 5);
            ?>
            <div style="overflow-x: auto;">
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
            <?php if ($course_count > 5): ?>
                <p style="text-align: center; margin-top: 20px; color: #666;">
                    <em>Showing <?php echo count($courses); ?> of <?php echo $course_count; ?> available courses</em>
                </p>
            <?php endif; ?>
        </div>

        <!-- Call to Action -->
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="cta-section">
                <h3 style="margin-bottom: 20px; color: #333;">Ready to Get Started?</h3>
                <a href="../auth/index.php" class="cta-button">Access Login Portal</a>
            </div>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>