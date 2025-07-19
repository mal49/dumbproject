<?php
// Database configuration
$host = 'localhost';
$dbname = 'course_registration';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Include utility classes
require_once __DIR__ . '/../utilities/CookieManager.php';
require_once __DIR__ . '/../utilities/AuthManager.php';
require_once __DIR__ . '/../utilities/DatabaseManager.php';
require_once __DIR__ . '/../utilities/FormValidator.php';

// Initialize utility instances
$authManager = new AuthManager($pdo);
$dbManager = new DatabaseManager($pdo);
?>