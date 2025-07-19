<?php
require_once '../config/config.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Check for auto-login
if (!AuthManager::isAuthenticated()) {
    $autoLoginUser = $authManager->autoLogin();
    if ($autoLoginUser) {
        AuthManager::redirectToDashboard();
    }
}

// If already logged in, redirect to dashboard
if (AuthManager::isAuthenticated()) {
    AuthManager::redirectToDashboard();
}

// Handle login form submission
if (isset($_POST['login'])) {
    $validator = new FormValidator();

    if ($validator->validateLogin($_POST)) {
        $result = $authManager->authenticate(
            $_POST['user_id'],
            $_POST['password'],
            $_POST['user_type']
        );

        if ($result['success']) {
            $authManager->createSession($result['user']);

            // Handle remember me
            if (isset($_POST['remember_me'])) {
                $userInfo = [
                    'id' => $_POST['user_id'],
                    'password' => $_POST['password'],
                    'type' => $_POST['user_type'],
                    'name' => $result['user']['name']
                ];
                CookieManager::setRememberMeCookies($userInfo);
            }

            AuthManager::redirectToDashboard();
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = implode(', ', $validator->getErrorMessages());
    }
}

// Handle signup form submission
if (isset($_POST['signup'])) {
    $validator = new FormValidator();

    if ($validator->validateStudentRegistration($_POST)) {
        try {
            // Check for duplicate email
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()['count'] > 0) {
                throw new Exception("Email already registered. Please use a different email.");
            }

            // Get next student ID and register
            $studentId = $dbManager->getNextStudentId();
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO student (Student_id, Name, Faculty_code, Programme_code, Campus, Semester, 
                Gender, Level_of_study, Mode_of_study, mailing_address, Postcode, mobile_phone_no, email, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $studentId,
                $_POST['name'],
                $_POST['faculty_code'],
                $_POST['programme_code'],
                $_POST['campus'],
                $_POST['semester'],
                $_POST['gender'],
                $_POST['level_of_study'],
                $_POST['mode_of_study'],
                $_POST['mailing_address'],
                $_POST['postcode'],
                FormValidator::cleanPhone($_POST['mobile_phone']),
                $_POST['email'],
                $hashedPassword
            ]);

            $success_message = "Registration successful! Your Student ID is: <strong>$studentId</strong><br>You can now login with your email and password.";

        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } else {
        $error_message = implode(', ', $validator->getErrorMessages());
    }
}

// Get next student ID for display
$nextStudentId = $dbManager->getNextStudentId();
$cookieData = CookieManager::getRememberMeData();

// Get all programmes from database for dropdown
try {
    $stmt = $pdo->query("SELECT Programme_code, Programme_name FROM programme ORDER BY Programme_name");
    $programmes = $stmt->fetchAll();
} catch (PDOException $e) {
    $programmes = [];
    error_log("Error fetching programmes: " . $e->getMessage());
}

// Get all faculties from database for dropdown
try {
    $stmt = $pdo->query("SELECT Faculty_code, Faculty_name FROM faculty ORDER BY Faculty_name");
    $faculties = $stmt->fetchAll();
} catch (PDOException $e) {
    $faculties = [];
    error_log("Error fetching faculties: " . $e->getMessage());
}

// Set page configuration
$pageTitle = 'Course Registration System - Login';
$cssFiles = ['forms', 'components', 'utilities'];

// Include header template
include '../includes/header.php';
?>

<div class="container">
    <div class="login-container">
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="showLogin()" class="btn" id="loginBtn">Login</button>
            <button onclick="showSignup()" class="btn" id="signupBtn">Sign Up</button>
        </div>

        <!-- Login Form -->
        <div id="loginForm">
            <h2 style="text-align: center; margin-bottom: 20px;">Login</h2>

            <?php if ($error_message && isset($_POST['login'])): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>User Type:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="user_type" value="student" <?php echo (!$cookieData || $cookieData['user_type'] === 'student') ? 'checked' : ''; ?>>
                            Student
                        </label>
                        <label>
                            <input type="radio" name="user_type" value="lecturer" <?php echo ($cookieData && $cookieData['user_type'] === 'lecturer') ? 'checked' : ''; ?>>
                            Lecturer
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <input type="text" name="user_id" id="user_id" required
                        value="<?php echo $cookieData ? htmlspecialchars($cookieData['user_id']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required
                        value="<?php echo $cookieData ? htmlspecialchars($cookieData['password']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; font-weight: normal;">
                        <input type="checkbox" name="remember_me" value="1" style="margin-right: 8px;" <?php echo $cookieData ? 'checked' : ''; ?>>
                        Remember me for 30 days
                    </label>
                </div>

                <button type="submit" name="login" class="btn" style="width: 100%;">Login</button>
            </form>
        </div>

        <!-- Signup Form -->
        <div id="signupForm" style="display: none;">
            <h2 style="text-align: center; margin-bottom: 20px;">Student Sign Up</h2>

            <div
                style="text-align: center; margin-bottom: 20px; padding: 15px; background-color: #e7f3ff; border: 1px solid #b3d7ff; border-radius: 5px;">
                <p style="margin: 0; color: #0066cc; font-weight: bold;">Your Student ID will be: <span
                        style="font-size: 1.2em; color: #0052a3;"><?php echo $nextStudentId; ?></span></p>

            </div>

            <?php if ($error_message && isset($_POST['signup'])): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateSignupForm()">
                <!-- Row 1: Name and Email -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" name="name" id="name" required
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

                    </div>
                </div>

                <!-- Row 2: Password and Faculty -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="signup_password" required>

                    </div>
                    <div class="form-group">
                        <label for="faculty_code">Faculty:</label>
                        <select name="faculty_code" required>
                            <option value="">Select Faculty</option>
                            <?php if (!empty($faculties)): ?>
                                <?php foreach ($faculties as $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['Faculty_code']); ?>"
                                        <?php echo (isset($_POST['faculty_code']) && $_POST['faculty_code'] === $faculty['Faculty_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($faculty['Faculty_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No faculties available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 3: Programme and Campus -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="programme_code">Programme:</label>
                        <select name="programme_code" required>
                            <option value="">Select Programme</option>
                            <?php if (!empty($programmes)): ?>
                                <?php foreach ($programmes as $programme): ?>
                                    <option value="<?php echo htmlspecialchars($programme['Programme_code']); ?>"
                                        <?php echo (isset($_POST['programme_code']) && $_POST['programme_code'] === $programme['Programme_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($programme['Programme_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No programmes available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="campus">Campus:</label>
                        <select name="campus" required>
                            <option value="">Select Campus</option>
                            <option value="Main">Main Campus</option>
                            <option value="North">North Campus</option>
                            <option value="South">South Campus</option>
                        </select>
                    </div>
                </div>

                <!-- Row 4: Semester and Gender -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                            <option value="5">Semester 5</option>
                            <option value="6">Semester 6</option>
                            <option value="7">Semester 7</option>
                            <option value="8">Semester 8</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- Row 5: Level and Mode of Study -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="level_of_study">Level of Study:</label>
                        <select name="level_of_study" required>
                            <option value="">Select Level</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Undergraduate">Undergraduate</option>
                            <option value="Postgraduate">Postgraduate</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mode_of_study">Mode of Study:</label>
                        <select name="mode_of_study" required>
                            <option value="">Select Mode</option>
                            <option value="Full Time">Full Time</option>
                            <option value="Part Time">Part Time</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: Address and Postcode -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="mailing_address">Mailing Address:</label>
                        <textarea name="mailing_address" id="mailing_address" rows="3" required
                            placeholder="Enter your complete mailing address..."><?php echo isset($_POST['mailing_address']) ? htmlspecialchars($_POST['mailing_address']) : ''; ?></textarea>

                    </div>
                    <div class="form-group">
                        <label for="postcode">Postcode:</label>
                        <input type="text" name="postcode" id="postcode" required pattern="\d{5}"
                            value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>">

                    </div>
                </div>

                <!-- Row 7: Mobile Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile_phone">Mobile Phone:</label>
                        <input type="tel" name="mobile_phone" id="mobile_phone" required
                            value="<?php echo isset($_POST['mobile_phone']) ? htmlspecialchars($_POST['mobile_phone']) : ''; ?>">

                    </div>
                </div>

                <button type="submit" name="signup" class="btn" style="width: 100%; margin-top: 20px;">Sign Up</button>
            </form>
        </div>
    </div>
</div>

<script>
    function showLogin() {
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('signupForm').style.display = 'none';
        document.getElementById('loginBtn').style.backgroundColor = '#007bff';
        document.getElementById('signupBtn').style.backgroundColor = '#6c757d';
    }

    function showSignup() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('signupForm').style.display = 'block';
        document.getElementById('loginBtn').style.backgroundColor = '#6c757d';
        document.getElementById('signupBtn').style.backgroundColor = '#007bff';
    }

    function validateSignupForm() {
        // Basic client-side validation
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('signup_password').value;
        const postcode = document.getElementById('postcode').value.trim();

        if (name.length < 2 || name.length > 100) {
            alert('Name must be between 2 and 100 characters');
            return false;
        }

        if (!/^[a-zA-Z\s]+$/.test(name)) {
            alert('Name must contain only letters and spaces');
            return false;
        }

        if (password.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }

        if (!/^\d{5}$/.test(postcode)) {
            alert('Postcode must be exactly 5 digits');
            return false;
        }

        return true;
    }

    // Show login form by default
    showLogin();
</script>

<?php include '../includes/footer.php'; ?>