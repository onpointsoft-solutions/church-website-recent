<?php
// CEFC Bible Study Management System
// File: auth/logout.php
// Description: User logout and session cleanup

require_once '../config/config.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset specific session variables
unset($_SESSION['bs_user_id']);
unset($_SESSION['bs_user_name']);
unset($_SESSION['bs_user_role']);
unset($_SESSION['bs_user_email']);
unset($_SESSION['bs_login_time']);

// Destroy session
session_destroy();

// Redirect to login with success message
header('Location: login.php?success=logged_out');
exit;