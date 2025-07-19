<?php
/**
 * Dynamic Navigation Template
 * Eliminates navigation duplication across pages
 */

// Define navigation items for different user types
$navigationItems = [
    'student' => [
        'dashboard.php' => 'Dashboard',
        'register_course.php' => 'Register Course',
        'my_courses.php' => 'My Courses',
        'about.php' => 'About'
    ],
    'lecturer' => [
        'lecturer_dashboard.php' => 'Dashboard',
        'about.php' => 'About'
    ],
    'guest' => [
        'about.php' => 'About'
    ]
];

// Determine current user type
$userType = 'guest';
if (isset($_SESSION['user_type'])) {
    $userType = $_SESSION['user_type'];
}

// Get current page name for active highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <div class="container">
        <ul>
            <?php foreach ($navigationItems[$userType] as $page => $title): ?>
                <?php $activeClass = ($currentPage === $page) ? 'class="active"' : ''; ?>
                <li><a href="<?php echo $page; ?>" <?php echo $activeClass; ?>><?php echo $title; ?></a></li>
            <?php endforeach; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/index.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>