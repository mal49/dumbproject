<?php
require_once '../config/config.php';

// Use the simplified AuthManager logout method
AuthManager::logout();

// Redirect to login page
header('Location: index.php');
exit;
?>