<?php
/**
 * AuthManager - Centralized authentication management
 * Simplifies complex password handling and login logic
 */
class AuthManager
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Authenticate user and handle both old and new password formats
     */
    public function authenticate($userId, $password, $userType)
    {
        if ($userType === 'student') {
            return $this->authenticateStudent($userId, $password);
        } else {
            return $this->authenticateLecturer($userId, $password);
        }
    }

    /**
     * Authenticate student with email or student ID
     */
    private function authenticateStudent($userId, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM student WHERE email = ? OR Student_id = ?");
        $stmt->execute([$userId, $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!$this->validatePassword($password, $user['password'], $user['Student_id'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }

        return [
            'success' => true,
            'user' => [
                'id' => $user['Student_id'],
                'name' => $user['Name'],
                'type' => 'student',
                'email' => $user['email']
            ]
        ];
    }

    /**
     * Authenticate lecturer
     */
    private function authenticateLecturer($userId, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lecturer WHERE lecturer_id = ?");
        $stmt->execute([$userId]);
        $lecturer = $stmt->fetch();

        if (!$lecturer) {
            return ['success' => false, 'message' => 'Lecturer not found'];
        }

        // Check if lecturer has a password field and validate it
        if (!isset($lecturer['password']) || !$this->validateLecturerPassword($password, $lecturer['password'], $lecturer['lecturer_id'])) {
            return ['success' => false, 'message' => 'Invalid lecturer credentials'];
        }

        return [
            'success' => true,
            'user' => [
                'id' => $lecturer['lecturer_id'],
                'name' => $lecturer['lecturer_name'],
                'type' => 'lecturer'
            ]
        ];
    }

    /**
     * Validate password and handle migration from plain text to hashed
     */
    private function validatePassword($inputPassword, $storedPassword, $studentId)
    {
        // Check if password is hashed (new accounts)
        if (password_verify($inputPassword, $storedPassword)) {
            return true;
        }

        // Check if it's a plain text password (old accounts)
        if ($storedPassword === $inputPassword) {
            // Migrate to hashed password for security
            $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE student SET password = ? WHERE Student_id = ?");
            $stmt->execute([$hashedPassword, $studentId]);
            return true;
        }

        return false;
    }

    /**
     * Validate lecturer password and handle migration from plain text to hashed
     */
    private function validateLecturerPassword($inputPassword, $storedPassword, $lecturerId)
    {
        // Check if password is hashed (new accounts)
        if (password_verify($inputPassword, $storedPassword)) {
            return true;
        }

        // Check if it's a plain text password (old accounts)
        if ($storedPassword === $inputPassword) {
            // Migrate to hashed password for security
            $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE lecturer SET password = ? WHERE lecturer_id = ?");
            $stmt->execute([$hashedPassword, $lecturerId]);
            return true;
        }

        return false;
    }

    /**
     * Auto-login using remember me cookies
     */
    public function autoLogin()
    {
        $cookieData = CookieManager::getRememberMeData();
        if (!$cookieData) {
            return false;
        }

        $result = $this->authenticate(
            $cookieData['user_id'],
            $cookieData['password'],
            $cookieData['user_type']
        );

        if ($result['success']) {
            $this->createSession($result['user']);
            return $result['user'];
        }

        return false;
    }

    /**
     * Create user session
     */
    public function createSession($userData)
    {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_type'] = $userData['type'];
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        return self::isAuthenticated() && $_SESSION['user_type'] === $role;
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser()
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'type' => $_SESSION['user_type']
        ];
    }

    /**
     * Logout user and clear session/cookies
     */
    public static function logout()
    {
        CookieManager::clearRememberMeCookies();
        session_destroy();
    }

    /**
     * Redirect to appropriate dashboard based on user type
     */
    public static function redirectToDashboard()
    {
        if (!self::isAuthenticated()) {
            header('Location: ../auth/index.php');
            exit;
        }

        if ($_SESSION['user_type'] === 'student') {
            header('Location: ../pages/dashboard.php');
        } else {
            header('Location: ../pages/lecturer_dashboard.php');
        }
        exit;
    }

    /**
     * Require authentication (for protected pages)
     */
    public static function requireAuth($requiredRole = null)
    {
        if (!self::isAuthenticated()) {
            header('Location: ../auth/index.php');
            exit;
        }

        if ($requiredRole && !self::hasRole($requiredRole)) {
            header('Location: ../auth/index.php');
            exit;
        }
    }
}