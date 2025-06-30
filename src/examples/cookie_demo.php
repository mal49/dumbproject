<?php
require_once '../config/config.php';

// Handle cookie clearing
if (isset($_POST['clear_cookies'])) {
    $cookies_to_clear = ['remember_user_id', 'remember_password', 'remember_user_type', 'remember_user_name'];
    foreach ($cookies_to_clear as $cookie) {
        if (isset($_COOKIE[$cookie])) {
            setcookie($cookie, '', time() - 3600, '/', '', false, true);
        }
    }
    header('Location: cookie_demo.php');
    exit;
}

// Set a demo cookie if form is submitted
if (isset($_POST['set_demo_cookie'])) {
    $cookie_name = $_POST['cookie_name'];
    $cookie_value = $_POST['cookie_value'];
    $expire_time = time() + (intval($_POST['expire_minutes']) * 60);

    setcookie($cookie_name, $cookie_value, $expire_time, '/', '', false, true);
    header('Location: cookie_demo.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Demonstration - Lab 9</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .cookie-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .cookie-name {
            font-weight: bold;
            color: #007bff;
        }

        .cookie-value {
            color: #28a745;
            word-break: break-all;
        }

        .no-cookies {
            color: #6c757d;
            font-style: italic;
        }

        .demo-section {
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #f0f8ff;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Cookie Demonstration - Lab 9</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="../auth/index.php">Back to Login</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a
                            href="<?php echo $_SESSION['user_type'] === 'student' ? '../pages/dashboard.php' : '../pages/lecturer_dashboard.php'; ?>">Dashboard</a>
                    </li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>🍪 Cookie Information & Management</h2>
            <p>This page demonstrates cookie functionality for Lab 9 PHP exercise.</p>
        </div>

        <!-- Current Cookies Display -->
        <div class="card">
            <h3>📋 Current Cookies</h3>
            <?php if (empty($_COOKIE)): ?>
                <p class="no-cookies">No cookies are currently set in your browser for this domain.</p>
            <?php else: ?>
                <p>The following cookies are currently stored in your browser:</p>
                <?php foreach ($_COOKIE as $name => $value): ?>
                    <div class="cookie-info">
                        <div class="cookie-name">Cookie Name: <?php echo htmlspecialchars($name); ?></div>
                        <div class="cookie-value">Value: <?php echo htmlspecialchars($value); ?></div>
                        <small class="text-muted">
                            <?php if (strpos($name, 'remember_') === 0): ?>
                                This is a "Remember Me" cookie for auto-login functionality.
                            <?php elseif ($name === 'PHPSESSID'): ?>
                                This is PHP's session cookie.
                            <?php else: ?>
                                This is a custom cookie.
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Remember Me Cookies Section -->
        <div class="card">
            <h3>🔐 Remember Me Cookies</h3>
            <?php
            $remember_cookies = ['remember_user_id', 'remember_password', 'remember_user_type', 'remember_user_name'];
            $has_remember_cookies = false;
            foreach ($remember_cookies as $cookie) {
                if (isset($_COOKIE[$cookie])) {
                    $has_remember_cookies = true;
                    break;
                }
            }
            ?>

            <?php if ($has_remember_cookies): ?>
                <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;">
                    <h4 style="color: #155724; margin-top: 0;">✅ Remember Me is Active</h4>
                    <p style="color: #155724;">The following login information is stored in cookies:</p>
                    <ul style="color: #155724;">
                        <?php if (isset($_COOKIE['remember_user_id'])): ?>
                            <li><strong>User ID:</strong> <?php echo htmlspecialchars($_COOKIE['remember_user_id']); ?></li>
                        <?php endif; ?>
                        <?php if (isset($_COOKIE['remember_user_name'])): ?>
                            <li><strong>User Name:</strong> <?php echo htmlspecialchars($_COOKIE['remember_user_name']); ?></li>
                        <?php endif; ?>
                        <?php if (isset($_COOKIE['remember_user_type'])): ?>
                            <li><strong>User Type:</strong> <?php echo htmlspecialchars($_COOKIE['remember_user_type']); ?></li>
                        <?php endif; ?>
                    </ul>
                    <p style="color: #155724; margin-bottom: 0;"><small>When you visit the login page, you'll be
                            automatically logged in using these stored credentials.</small></p>
                </div>

                <form method="POST" style="margin-top: 15px;">
                    <button type="submit" name="clear_cookies" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to clear all Remember Me cookies?')">
                        Clear Remember Me Cookies
                    </button>
                </form>
            <?php else: ?>
                <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <p style="color: #721c24; margin: 0;">❌ No Remember Me cookies are currently set. To activate this
                        feature, go to the <a href="../auth/index.php">login page</a> and check the "Remember me for 30
                        days"
                        checkbox when logging in.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cookie Demonstration Section -->
        <div class="demo-section">
            <h3>🧪 Cookie Testing Lab</h3>
            <p>Use this section to experiment with creating custom cookies:</p>

            <form method="POST">
                <div class="form-group">
                    <label for="cookie_name">Cookie Name:</label>
                    <input type="text" name="cookie_name" id="cookie_name" required placeholder="e.g. test_cookie">
                </div>

                <div class="form-group">
                    <label for="cookie_value">Cookie Value:</label>
                    <input type="text" name="cookie_value" id="cookie_value" required placeholder="e.g. Hello World">
                </div>

                <div class="form-group">
                    <label for="expire_minutes">Expiration (minutes):</label>
                    <select name="expire_minutes" id="expire_minutes" required>
                        <option value="5">5 minutes</option>
                        <option value="30">30 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="1440">1 day</option>
                    </select>
                </div>

                <button type="submit" name="set_demo_cookie" class="btn">Set Cookie</button>
            </form>
        </div>

        <!-- Lab Exercise Information -->
        <div class="card">
            <h3>📚 Lab 9 Exercise Summary</h3>
            <h4>Cookie Features Implemented:</h4>
            <ul>
                <li>✅ <strong>Remember Me functionality</strong> - Stores login credentials in cookies for 30 days</li>
                <li>✅ <strong>Auto-login</strong> - Automatically logs users in when remember me cookies are present
                </li>
                <li>✅ <strong>Cookie security</strong> - HTTPOnly flag set to prevent JavaScript access</li>
                <li>✅ <strong>Cookie management</strong> - Proper clearing of cookies on logout</li>
                <li>✅ <strong>Cookie demonstration</strong> - This page shows all cookie operations</li>
                <li>✅ <strong>User feedback</strong> - Visual indicators when auto-login occurs</li>
            </ul>

            <h4>How to Test:</h4>
            <ol>
                <li>Go to the <a href="index.php">login page</a></li>
                <li>Enter valid credentials (e.g., Student ID: ST001, Password: password123)</li>
                <li>Check the "Remember me for 30 days" checkbox</li>
                <li>Click Login</li>
                <li>After successful login, <a href="logout.php">logout</a></li>
                <li>Return to the <a href="index.php">login page</a> - you should be automatically logged in</li>
                <li>Visit this page again to see the Remember Me cookies</li>
            </ol>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Registration System - Lab 9 Cookie Exercise</p>
        </div>
    </footer>
</body>

</html>