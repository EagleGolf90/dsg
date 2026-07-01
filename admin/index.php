<?php
/**
 * Admin Index
 * Redirects to login page or dashboard depending on authentication
 */

// Start session
session_start();

// Check if user is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // User is logged in, redirect to dashboard
    header("Location: dashboard.php");
} else {
    // User is not logged in, redirect to login page
    header("Location: login.php");
}
exit();
?>