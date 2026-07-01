<?php
/**
 * Authentication Check
 * Include this file at the top of every admin page to protect it
 * Usage: require_once 'check_auth.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Check if session has timed out (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session has timed out
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>
