<?php
require_once '../config/config.php';

// Clear remember me cookies if they exist
if (isset($_COOKIE['remember_user_id'])) {
    setcookie('remember_user_id', '', time() - 3600, '/', '', false, true);
}
if (isset($_COOKIE['remember_password'])) {
    setcookie('remember_password', '', time() - 3600, '/', '', false, true);
}
if (isset($_COOKIE['remember_user_type'])) {
    setcookie('remember_user_type', '', time() - 3600, '/', '', false, true);
}
if (isset($_COOKIE['remember_user_name'])) {
    setcookie('remember_user_name', '', time() - 3600, '/', '', false, true);
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: index.php');
exit;
?>