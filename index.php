<?php require_once 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration System - Login</title>
    <link rel="stylesheet" href="styles.css">
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
                if (isset($_POST['login'])) {
                    $user_id = $_POST['user_id'];
                    $password = $_POST['password'];
                    $user_type = $_POST['user_type'];

                    if ($user_type === 'student') {
                        $stmt = $pdo->prepare("SELECT * FROM student WHERE Student_id = ? AND password = ?");
                        $stmt->execute([$user_id, $password]);
                        $user = $stmt->fetch();

                        if ($user) {
                            $_SESSION['user_id'] = $user['Student_id'];
                            $_SESSION['user_name'] = $user['Name'];
                            $_SESSION['user_type'] = 'student';
                            header('Location: dashboard.php');
                            exit;
                        } else {
                            echo '<div class="alert alert-danger">Invalid student credentials!</div>';
                        }
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM lecturer WHERE lecturer_id = ?");
                        $stmt->execute([$user_id]);
                        $lecturer = $stmt->fetch();

                        if ($lecturer && $password === 'lecturer123') { // Simple password for demo
                            $_SESSION['user_id'] = $lecturer['lecturer_id'];
                            $_SESSION['user_name'] = $lecturer['lecturer_name'];
                            $_SESSION['user_type'] = 'lecturer';
                            header('Location: lecturer_dashboard.php');
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
                                <input type="radio" name="user_type" value="student" checked>
                                Student
                            </label>
                            <label>
                                <input type="radio" name="user_type" value="lecturer">
                                Lecturer
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_id">User ID:</label>
                        <input type="text" name="user_id" id="user_id" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
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
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $password = $_POST['password'];
                    $faculty_code = $_POST['faculty_code'];
                    $programme_code = $_POST['programme_code'];
                    $campus = $_POST['campus'];
                    $semester = $_POST['semester'];
                    $gender = $_POST['gender'];
                    $level_of_study = $_POST['level_of_study'];
                    $mode_of_study = $_POST['mode_of_study'];
                    $mailing_address = $_POST['mailing_address'];
                    $postcode = $_POST['postcode'];
                    $mobile_phone = $_POST['mobile_phone'];

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

                        // Insert with generated Student_id
                        $stmt = $pdo->prepare("INSERT INTO student (Student_id, Name, Faculty_code, Programme_code, Campus, Semester, Gender, Level_of_study, Mode_of_study, mailing_address, Postcode, mobile_phone_no, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$student_id, $name, $faculty_code, $programme_code, $campus, $semester, $gender, $level_of_study, $mode_of_study, $mailing_address, $postcode, $mobile_phone, $email, $password]);

                        echo '<div class="alert alert-success">Registration successful! Your Student ID is: <strong>' . $student_id . '</strong><br>You can now login.</div>';
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Registration failed: ' . $e->getMessage() . '</div>';
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" name="name" id="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="form-group">
                        <label for="faculty_code">Faculty:</label>
                        <select name="faculty_code" required>
                            <option value="">Select Faculty</option>
                            <option value="KPPIM">KPPIM - Kolej Pengajian Pengkomputeran, Informatik dan Matematik
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="programme_code">Programme:</label>
                        <select name="programme_code" required>
                            <option value="">Select Programme</option>
                            <option value="CS110">CS110 - Diploma of Computer Science</option>
                            <option value="CS230">CS230 - Bachelor Degree in Computer Science</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="campus">Campus:</label>
                        <select name="campus" required>
                            <option value="">Select Campus</option>
                            <option value="Shah Alam">Shah Alam</option>
                            <option value="Melaka">Melaka</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <input type="number" name="semester" min="1" max="8" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="level_of_study">Level of Study:</label>
                        <select name="level_of_study" required>
                            <option value="">Select Level</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Degree">Degree</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mode_of_study">Mode of Study:</label>
                        <select name="mode_of_study" required>
                            <option value="">Select Mode</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mailing_address">Mailing Address:</label>
                        <input type="text" name="mailing_address" required>
                    </div>

                    <div class="form-group">
                        <label for="postcode">Postcode:</label>
                        <input type="text" name="postcode" required>
                    </div>

                    <div class="form-group">
                        <label for="mobile_phone">Mobile Phone:</label>
                        <input type="text" name="mobile_phone" required>
                    </div>

                    <button type="submit" name="signup" class="btn" style="width: 100%;">Sign Up</button>
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
    </script>
</body>

</html>