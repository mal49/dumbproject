<?php
require_once '../config/config.php';

// Use simplified authentication check
AuthManager::requireAuth('student');

// Set page configuration
$pageTitle = 'Update Profile - Course Registration System';
$cssFiles = ['forms', 'components', 'utilities'];

// Initialize variables
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $faculty_code = trim($_POST['faculty_code'] ?? '');
    $campus = trim($_POST['campus'] ?? '');
    $mailing_address = trim($_POST['mailing_address'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $mobile_phone_no = trim($_POST['mobile_phone_no'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validate input
    $validator = new FormValidator();
    $validator->required($name, 'Name');
    $validator->required($faculty_code, 'Faculty code');
    $validator->required($campus, 'Campus');
    $validator->required($mailing_address, 'Mailing address');
    $validator->required($postcode, 'Postcode');
    $validator->required($mobile_phone_no, 'Mobile phone number');
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validator->addError('email', 'Invalid email format');
    }

    if (!$validator->hasErrors()) {
        try {
            // Update student details in the database
            $success = $dbManager->updateStudentProfile(
                $_SESSION['user_id'],
                [
                    'name' => $name,
                    'faculty_code' => $faculty_code,
                    'campus' => $campus,
                    'mailing_address' => $mailing_address,
                    'postcode' => $postcode,
                    'mobile_phone_no' => $mobile_phone_no,
                    'email' => $email
                ]
            );

            if ($success) {
                $_SESSION['user_name'] = $name; // Update session with new name
                $message = 'Profile updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update profile. Please try again.';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'An error occurred while updating your profile.';
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $validator->getErrorMessages());
        $messageType = 'error';
    }
}

// Get current student details
$studentDetails = $dbManager->getStudentDetails($_SESSION['user_id']);

// Include header template
include '../includes/header.php';

// Include navigation template
include '../includes/navigation.php';
?>

<div class="container">
    <div class="card">
        <h2>Update Profile</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($studentDetails['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="faculty_code">Faculty Code:</label>
                <input type="text" id="faculty_code" name="faculty_code" value="<?php echo htmlspecialchars($studentDetails['faculty_code'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="campus">Campus:</label>
                <input type="text" id="campus" name="campus" value="<?php echo htmlspecialchars($studentDetails['campus'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Gender:</label>
                <p class="form-control-static"><?php echo htmlspecialchars($studentDetails['gender'] ?? ''); ?></p>
            </div>

            <div class="form-group">
                <label>Level of Study:</label>
                <p class="form-control-static"><?php echo htmlspecialchars($studentDetails['level_of_study'] ?? ''); ?></p>
            </div>

            <div class="form-group">
                <label>Mode of Study:</label>
                <p class="form-control-static"><?php echo htmlspecialchars($studentDetails['mode_of_study'] ?? ''); ?></p>
            </div>

            <div class="form-group">
                <label for="mailing_address">Mailing Address:</label>
                <textarea id="mailing_address" name="mailing_address" required><?php echo htmlspecialchars($studentDetails['mailing_address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="postcode">Postcode:</label>
                <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($studentDetails['postcode'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="mobile_phone_no">Mobile Phone Number:</label>
                <input type="tel" id="mobile_phone_no" name="mobile_phone_no" value="<?php echo htmlspecialchars($studentDetails['mobile_phone_no'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($studentDetails['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
