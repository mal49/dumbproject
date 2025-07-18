<?php require_once '../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration System - Login</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Course Registration System</h1>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <div style="text-align: center; margin-bottom: 20px;">
                <button onclick="showLogin()" class="btn" id="loginBtn">Login</button>
                <button onclick="showSignup()" class="btn" id="signupBtn">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div id="loginForm">
                <h2 style="text-align: center; margin-bottom: 20px;">Login</h2>

                <?php
                // Check for remember me cookies on page load
                if (!isset($_POST['login']) && isset($_COOKIE['remember_user_id']) && isset($_COOKIE['remember_password']) && isset($_COOKIE['remember_user_type'])) {
                    $user_id = $_COOKIE['remember_user_id'];
                    $password = $_COOKIE['remember_password'];
                    $user_type = $_COOKIE['remember_user_type'];

                    // Auto-login with remembered credentials
                    if ($user_type === 'student') {
                        // Try login with email first (for new registrations), then with Student_id (for older accounts)
                        $stmt = $pdo->prepare("SELECT * FROM student WHERE email = ? OR Student_id = ?");
                        $stmt->execute([$user_id, $user_id]);
                        $user = $stmt->fetch();

                        if ($user) {
                            // Check if password is hashed (new accounts) or plain text (old accounts)
                            $password_valid = false;
                            if (password_verify($password, $user['password'])) {
                                // New hashed password
                                $password_valid = true;
                            } elseif ($user['password'] === $password) {
                                // Old plain text password
                                $password_valid = true;
                            }

                            if ($password_valid) {
                                $_SESSION['user_id'] = $user['Student_id'];
                                $_SESSION['user_name'] = $user['Name'];
                                $_SESSION['user_type'] = 'student';
                                header('Location: ../pages/dashboard.php');
                                exit;
                            }
                        }
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM lecturer WHERE lecturer_id = ?");
                        $stmt->execute([$user_id]);
                        $lecturer = $stmt->fetch();

                        if ($lecturer && $password === 'lecturer123') {
                            $_SESSION['user_id'] = $lecturer['lecturer_id'];
                            $_SESSION['user_name'] = $lecturer['lecturer_name'];
                            $_SESSION['user_type'] = 'lecturer';
                            header('Location: ../pages/lecturer_dashboard.php');
                            exit;
                        }
                    }
                }

                if (isset($_POST['login'])) {
                    $user_id = $_POST['user_id'];
                    $password = $_POST['password'];
                    $user_type = $_POST['user_type'];
                    $remember_me = isset($_POST['remember_me']);

                    if ($user_type === 'student') {
                        // Try login with email first (for new registrations), then with Student_id (for older accounts)
                        $stmt = $pdo->prepare("SELECT * FROM student WHERE email = ? OR Student_id = ?");
                        $stmt->execute([$user_id, $user_id]);
                        $user = $stmt->fetch();

                        if ($user) {
                            // Check if password is hashed (new accounts) or plain text (old accounts)
                            $password_valid = false;
                            if (password_verify($password, $user['password'])) {
                                // New hashed password
                                $password_valid = true;
                            } elseif ($user['password'] === $password) {
                                // Old plain text password - rehash it for security
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $update_stmt = $pdo->prepare("UPDATE student SET password = ? WHERE Student_id = ?");
                                $update_stmt->execute([$hashed_password, $user['Student_id']]);
                                $password_valid = true;
                            }

                            if ($password_valid) {
                                $_SESSION['user_id'] = $user['Student_id'];
                                $_SESSION['user_name'] = $user['Name'];
                                $_SESSION['user_type'] = 'student';

                                // Set remember me cookies if checkbox was checked
                                if ($remember_me) {
                                    $expire_time = time() + (30 * 24 * 60 * 60); // 30 days
                                    setcookie('remember_user_id', $user_id, $expire_time, '/', '', false, true);
                                    setcookie('remember_password', $password, $expire_time, '/', '', false, true);
                                    setcookie('remember_user_type', $user_type, $expire_time, '/', '', false, true);
                                    setcookie('remember_user_name', $user['Name'], $expire_time, '/', '', false, true);
                                }

                                header('Location: ../pages/dashboard.php');
                                exit;
                            }
                        }
                        echo '<div class="alert alert-danger">Invalid credentials! Please check your email/Student ID and password.</div>';
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM lecturer WHERE lecturer_id = ?");
                        $stmt->execute([$user_id]);
                        $lecturer = $stmt->fetch();

                        if ($lecturer && $password === 'lecturer123') { // Simple password for demo
                            $_SESSION['user_id'] = $lecturer['lecturer_id'];
                            $_SESSION['user_name'] = $lecturer['lecturer_name'];
                            $_SESSION['user_type'] = 'lecturer';

                            // Set remember me cookies if checkbox was checked
                            if ($remember_me) {
                                $expire_time = time() + (30 * 24 * 60 * 60); // 30 days
                                setcookie('remember_user_id', $user_id, $expire_time, '/', '', false, true);
                                setcookie('remember_password', $password, $expire_time, '/', '', false, true);
                                setcookie('remember_user_type', $user_type, $expire_time, '/', '', false, true);
                                setcookie('remember_user_name', $lecturer['lecturer_name'], $expire_time, '/', '', false, true);
                            }

                            header('Location: ../pages/lecturer_dashboard.php');
                            exit;
                        } else {
                            echo '<div class="alert alert-danger">Invalid lecturer credentials!</div>';
                        }
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-group">
                        <label>User Type:</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="user_type" value="student" <?php echo (!isset($_COOKIE['remember_user_type']) || $_COOKIE['remember_user_type'] === 'student') ? 'checked' : ''; ?>>
                                Student
                            </label>
                            <label>
                                <input type="radio" name="user_type" value="lecturer" <?php echo (isset($_COOKIE['remember_user_type']) && $_COOKIE['remember_user_type'] === 'lecturer') ? 'checked' : ''; ?>>
                                Lecturer
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_id">User ID:</label>
                        <input type="text" name="user_id" id="user_id" required
                            value="<?php echo isset($_COOKIE['remember_user_id']) ? htmlspecialchars($_COOKIE['remember_user_id']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required
                            value="<?php echo isset($_COOKIE['remember_password']) ? htmlspecialchars($_COOKIE['remember_password']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; font-weight: normal;">
                            <input type="checkbox" name="remember_me" value="1" style="margin-right: 8px;" <?php echo isset($_COOKIE['remember_user_id']) ? 'checked' : ''; ?>>
                            Remember me for 30 days
                        </label>
                    </div>

                    <button type="submit" name="login" class="btn" style="width: 100%;">Login</button>
                </form>
            </div>

            <!-- Signup Form -->
            <div id="signupForm" style="display: none;">
                <h2 style="text-align: center; margin-bottom: 20px;">Student Sign Up</h2>

                <?php
                // Get the next student ID by looking at the last entry
                $next_student_id = "ST001"; // Default fallback
                try {
                    $stmt = $pdo->query("SELECT Student_id FROM student ORDER BY Student_id DESC LIMIT 1");
                    $result = $stmt->fetch();
                    if ($result) {
                        // Extract number from last Student_id (e.g., "ST005" -> 5)
                        $last_number = intval(substr($result['Student_id'], 2));
                        $next_number = $last_number + 1;
                        $next_student_id = 'ST' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
                    }
                } catch (PDOException $e) {
                    // If table doesn't exist yet or any error, keep default
                }
                ?>

                <div
                    style="text-align: center; margin-bottom: 20px; padding: 15px; background-color: #e7f3ff; border: 1px solid #b3d7ff; border-radius: 5px;">
                    <p style="margin: 0; color: #0066cc; font-weight: bold;">Your Student ID will be: <span
                            style="font-size: 1.2em; color: #0052a3;"><?php echo $next_student_id; ?></span></p>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;"><em>Please note this down for future
                            login</em></p>
                </div>

                <?php
                if (isset($_POST['signup'])) {
                    $errors = [];

                    // Sanitize and validate input
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $password = $_POST['password'];
                    $faculty_code = $_POST['faculty_code'];
                    $programme_code = $_POST['programme_code'];
                    $campus = $_POST['campus'];
                    $semester = $_POST['semester'];
                    $gender = $_POST['gender'];
                    $level_of_study = $_POST['level_of_study'];
                    $mode_of_study = $_POST['mode_of_study'];
                    $mailing_address = trim($_POST['mailing_address']);
                    $postcode = trim($_POST['postcode']);
                    $mobile_phone = trim($_POST['mobile_phone']);

                    // Validate name
                    if (empty($name)) {
                        $errors[] = "Full name is required.";
                    } elseif (strlen($name) < 2) {
                        $errors[] = "Full name must be at least 2 characters long.";
                    } elseif (strlen($name) > 100) {
                        $errors[] = "Full name must not exceed 100 characters.";
                    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
                        $errors[] = "Full name can only contain letters and spaces.";
                    }

                    // Validate email
                    if (empty($email)) {
                        $errors[] = "Email is required.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Please enter a valid email address.";
                    } else {
                        // Check if email already exists
                        try {
                            $stmt = $pdo->prepare("SELECT email FROM student WHERE email = ?");
                            $stmt->execute([$email]);
                            if ($stmt->fetch()) {
                                $errors[] = "Email address is already registered.";
                            }
                        } catch (PDOException $e) {
                            $errors[] = "Database error occurred while checking email.";
                        }
                    }

                    // Validate password
                    if (empty($password)) {
                        $errors[] = "Password is required.";
                    } elseif (strlen($password) < 6) {
                        $errors[] = "Password must be at least 6 characters long.";
                    } elseif (strlen($password) > 255) {
                        $errors[] = "Password must not exceed 255 characters.";
                    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/", $password)) {
                        $errors[] = "Password must contain at least one lowercase letter, one uppercase letter, and one number.";
                    }

                    // Validate faculty code
                    $valid_faculties = ['KPPIM'];
                    if (empty($faculty_code)) {
                        $errors[] = "Faculty selection is required.";
                    } elseif (!in_array($faculty_code, $valid_faculties)) {
                        $errors[] = "Please select a valid faculty.";
                    }

                    // Validate programme code
                    $valid_programmes = ['CS110', 'CS230'];
                    if (empty($programme_code)) {
                        $errors[] = "Programme selection is required.";
                    } elseif (!in_array($programme_code, $valid_programmes)) {
                        $errors[] = "Please select a valid programme.";
                    }

                    // Validate campus
                    $valid_campuses = ['Shah Alam', 'Melaka'];
                    if (empty($campus)) {
                        $errors[] = "Campus selection is required.";
                    } elseif (!in_array($campus, $valid_campuses)) {
                        $errors[] = "Please select a valid campus.";
                    }

                    // Validate semester
                    if (empty($semester)) {
                        $errors[] = "Semester is required.";
                    } elseif (!is_numeric($semester) || $semester < 1 || $semester > 8) {
                        $errors[] = "Semester must be a number between 1 and 8.";
                    }

                    // Validate gender
                    $valid_genders = ['Male', 'Female'];
                    if (empty($gender)) {
                        $errors[] = "Gender selection is required.";
                    } elseif (!in_array($gender, $valid_genders)) {
                        $errors[] = "Please select a valid gender.";
                    }

                    // Validate level of study
                    $valid_levels = ['Diploma', 'Degree'];
                    if (empty($level_of_study)) {
                        $errors[] = "Level of study selection is required.";
                    } elseif (!in_array($level_of_study, $valid_levels)) {
                        $errors[] = "Please select a valid level of study.";
                    }

                    // Validate mode of study
                    $valid_modes = ['Full-time', 'Part-time'];
                    if (empty($mode_of_study)) {
                        $errors[] = "Mode of study selection is required.";
                    } elseif (!in_array($mode_of_study, $valid_modes)) {
                        $errors[] = "Please select a valid mode of study.";
                    }

                    // Validate mailing address
                    if (empty($mailing_address)) {
                        $errors[] = "Mailing address is required.";
                    } elseif (strlen($mailing_address) < 10) {
                        $errors[] = "Mailing address must be at least 10 characters long.";
                    } elseif (strlen($mailing_address) > 255) {
                        $errors[] = "Mailing address must not exceed 255 characters.";
                    }

                    // Validate postcode
                    if (empty($postcode)) {
                        $errors[] = "Postcode is required.";
                    } elseif (!preg_match("/^\d{5}$/", $postcode)) {
                        $errors[] = "Postcode must be exactly 5 digits.";
                    }

                    // Validate mobile phone
                    if (empty($mobile_phone)) {
                        $errors[] = "Mobile phone is required.";
                    } elseif (!preg_match("/^01[0-9]-\d{7,8}$/", $mobile_phone)) {
                        $errors[] = "Mobile phone must be in format 01X-XXXXXXX or 01X-XXXXXXXX (Malaysian format).";
                    }

                    // Display errors or proceed with registration
                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        echo '<strong>Please fix the following errors:</strong><ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    } else {
                        try {
                            // Generate Student_id by looking at the last entry
                            $stmt = $pdo->query("SELECT Student_id FROM student ORDER BY Student_id DESC LIMIT 1");
                            $result = $stmt->fetch();

                            if ($result) {
                                // Extract number from last Student_id and increment
                                $last_number = intval(substr($result['Student_id'], 2));
                                $next_number = $last_number + 1;
                            } else {
                                // First student
                                $next_number = 1;
                            }

                            $student_id = 'ST' . str_pad($next_number, 3, '0', STR_PAD_LEFT);

                            // Hash the password for security
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            // Insert with generated Student_id
                            $stmt = $pdo->prepare("INSERT INTO student (Student_id, Name, Faculty_code, Programme_code, Campus, Semester, Gender, Level_of_study, Mode_of_study, mailing_address, Postcode, mobile_phone_no, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$student_id, $name, $faculty_code, $programme_code, $campus, $semester, $gender, $level_of_study, $mode_of_study, $mailing_address, $postcode, $mobile_phone, $email, $hashed_password]);

                            echo '<div class="alert alert-success">Registration successful! Your Student ID is: <strong>' . htmlspecialchars($student_id) . '</strong><br>You can now login with your email and password.</div>';

                            // Clear form data after successful registration
                            $_POST = [];
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">Registration failed: Database error occurred. Please try again later.</div>';
                        }
                    }
                }
                ?>

                <form method="POST" id="signupForm_form" onsubmit="return validateSignupForm()">
                    <!-- Row 1: Name and Email -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name:</label>
                            <input type="text" name="name" id="name" required
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            <small class="form-text">Must contain only letters and spaces, 2-100 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <small class="form-text">Must be a valid email address</small>
                        </div>
                    </div>

                    <!-- Row 2: Password and Faculty -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" required>
                            <small class="form-text">At least 6 characters with uppercase, lowercase, and number</small>
                        </div>

                        <div class="form-group">
                            <label for="faculty_code">Faculty:</label>
                            <select name="faculty_code" required>
                                <option value="">Select Faculty</option>
                                <option value="KPPIM" <?php echo (isset($_POST['faculty_code']) && $_POST['faculty_code'] === 'KPPIM') ? 'selected' : ''; ?>>KPPIM - Kolej Pengajian
                                    Pengkomputeran, Informatik dan Matematik
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Programme and Campus -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="programme_code">Programme:</label>
                            <select name="programme_code" required>
                                <option value="">Select Programme</option>
                                <option value="CS110" <?php echo (isset($_POST['programme_code']) && $_POST['programme_code'] === 'CS110') ? 'selected' : ''; ?>>CS110 - Diploma of
                                    Computer
                                    Science</option>
                                <option value="CS230" <?php echo (isset($_POST['programme_code']) && $_POST['programme_code'] === 'CS230') ? 'selected' : ''; ?>>CS230 - Bachelor Degree in
                                    Computer Science</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="campus">Campus:</label>
                            <select name="campus" required>
                                <option value="">Select Campus</option>
                                <option value="Shah Alam" <?php echo (isset($_POST['campus']) && $_POST['campus'] === 'Shah Alam') ? 'selected' : ''; ?>>Shah Alam</option>
                                <option value="Melaka" <?php echo (isset($_POST['campus']) && $_POST['campus'] === 'Melaka') ? 'selected' : ''; ?>>Melaka</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 4: Semester and Gender -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="semester">Semester:</label>
                            <input type="number" name="semester" min="1" max="8" required
                                value="<?php echo isset($_POST['semester']) ? htmlspecialchars($_POST['semester']) : ''; ?>">
                            <small class="form-text">Enter a number between 1 and 8</small>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 5: Level and Mode of Study -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="level_of_study">Level of Study:</label>
                            <select name="level_of_study" required>
                                <option value="">Select Level</option>
                                <option value="Diploma" <?php echo (isset($_POST['level_of_study']) && $_POST['level_of_study'] === 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                                <option value="Degree" <?php echo (isset($_POST['level_of_study']) && $_POST['level_of_study'] === 'Degree') ? 'selected' : ''; ?>>Degree</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="mode_of_study">Mode of Study:</label>
                            <select name="mode_of_study" required>
                                <option value="">Select Mode</option>
                                <option value="Full-time" <?php echo (isset($_POST['mode_of_study']) && $_POST['mode_of_study'] === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo (isset($_POST['mode_of_study']) && $_POST['mode_of_study'] === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            </select>
                        </div>
                    </div>

                    <!-- Full width: Mailing Address -->
                    <div class="form-group full-width">
                        <label for="mailing_address">Mailing Address:</label>
                        <input type="text" name="mailing_address" required
                            value="<?php echo isset($_POST['mailing_address']) ? htmlspecialchars($_POST['mailing_address']) : ''; ?>">
                        <small class="form-text">At least 10 characters, maximum 255 characters</small>
                    </div>

                    <!-- Row 6: Postcode and Mobile Phone -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="postcode">Postcode:</label>
                            <input type="text" name="postcode" required pattern="[0-9]{5}" placeholder="e.g., 40000"
                                value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>">
                            <small class="form-text">Must be exactly 5 digits</small>
                        </div>

                        <div class="form-group">
                            <label for="mobile_phone">Mobile Phone:</label>
                            <input type="text" name="mobile_phone" required pattern="01[0-9]-[0-9]{7,8}"
                                placeholder="e.g., 012-3456789"
                                value="<?php echo isset($_POST['mobile_phone']) ? htmlspecialchars($_POST['mobile_phone']) : ''; ?>">
                            <small class="form-text">Format: 01X-XXXXXXX or 01X-XXXXXXXX (Malaysian format)</small>
                        </div>
                    </div>

                    <button type="submit" name="signup" class="btn" style="width: 100%; margin-top: 20px;">Sign
                        Up</button>
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
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const postcode = document.querySelector('input[name="postcode"]').value.trim();
            const mobilePhone = document.querySelector('input[name="mobile_phone"]').value.trim();

            let errors = [];

            // Validate name
            if (name.length < 2 || name.length > 100) {
                errors.push('Full name must be between 2 and 100 characters.');
            }
            if (!/^[a-zA-Z\s]+$/.test(name)) {
                errors.push('Full name can only contain letters and spaces.');
            }

            // Validate email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('Please enter a valid email address.');
            }

            // Validate password
            if (password.length < 6) {
                errors.push('Password must be at least 6 characters long.');
            }
            if (!/(?=.*[a-z])/.test(password)) {
                errors.push('Password must contain at least one lowercase letter.');
            }
            if (!/(?=.*[A-Z])/.test(password)) {
                errors.push('Password must contain at least one uppercase letter.');
            }
            if (!/(?=.*\d)/.test(password)) {
                errors.push('Password must contain at least one number.');
            }

            // Validate postcode
            if (!/^\d{5}$/.test(postcode)) {
                errors.push('Postcode must be exactly 5 digits.');
            }

            // Validate mobile phone
            if (!/^01[0-9]-\d{7,8}$/.test(mobilePhone)) {
                errors.push('Mobile phone must be in format 01X-XXXXXXX or 01X-XXXXXXXX.');
            }

            if (errors.length > 0) {
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
                return false;
            }

            return true;
        }

        // Real-time validation feedback
        document.addEventListener('DOMContentLoaded', function () {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.addEventListener('input', function () {
                    const password = this.value;
                    const feedback = this.parentNode.querySelector('.password-feedback');

                    if (!feedback) {
                        const feedbackDiv = document.createElement('div');
                        feedbackDiv.className = 'password-feedback';
                        feedbackDiv.style.fontSize = '12px';
                        feedbackDiv.style.marginTop = '5px';
                        this.parentNode.appendChild(feedbackDiv);
                    }

                    const feedbackElement = this.parentNode.querySelector('.password-feedback');
                    let feedbackText = '';
                    let isValid = true;

                    if (password.length < 6) {
                        feedbackText += '• At least 6 characters needed<br>';
                        isValid = false;
                    }
                    if (!/(?=.*[a-z])/.test(password)) {
                        feedbackText += '• Need lowercase letter<br>';
                        isValid = false;
                    }
                    if (!/(?=.*[A-Z])/.test(password)) {
                        feedbackText += '• Need uppercase letter<br>';
                        isValid = false;
                    }
                    if (!/(?=.*\d)/.test(password)) {
                        feedbackText += '• Need number<br>';
                        isValid = false;
                    }

                    if (isValid && password.length > 0) {
                        feedbackText = '✓ Password meets requirements';
                        feedbackElement.style.color = 'green';
                    } else {
                        feedbackElement.style.color = 'red';
                    }

                    feedbackElement.innerHTML = feedbackText;
                });
            }
        });
    </script>
</body>

</html>