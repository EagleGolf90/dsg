<?php
/**
 * Process Admin User Actions
 * Handles add, edit, delete, and status toggle for admin users
 */

// Check authentication
require_once 'check_auth.php';

// Include database configuration
require_once '../includes/config.php';

// Create database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if action is set
if (!isset($_POST['action'])) {
  header("Location: manage_users.php?error=no_action");
  exit();
}

$action = $_POST['action'];

// Process based on action
switch ($action) {
  case 'add':
    addUser($conn);
    break;

  case 'edit':
    editUser($conn);
    break;

  case 'delete':
    deleteUser($conn);
    break;

  case 'toggle_status':
    toggleStatus($conn);
    break;

  default:
    header("Location: manage_users.php?error=invalid_action");
    exit();
}

$conn->close();

/**
 * Add new admin user
 */
function addUser($conn)
{
  // Validate required fields
  if (
    !isset($_POST['username']) || !isset($_POST['email']) ||
    !isset($_POST['full_name']) || !isset($_POST['password']) ||
    !isset($_POST['password_confirm']) || !isset($_POST['is_active'])
  ) {
    header("Location: manage_users.php?error=missing_fields");
    exit();
  }

  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $full_name = trim($_POST['full_name']);
  $password = $_POST['password'];
  $password_confirm = $_POST['password_confirm'];
  $is_active = intval($_POST['is_active']);

  // Validate username format
  if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    header("Location: manage_users.php?error=invalid_username");
    exit();
  }

  // Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: manage_users.php?error=invalid_email");
    exit();
  }

  // Validate password length
  if (strlen($password) < 8) {
    header("Location: manage_users.php?error=password_too_short");
    exit();
  }

  // Check if passwords match
  if ($password !== $password_confirm) {
    header("Location: manage_users.php?error=password_mismatch");
    exit();
  }

  // Check if username already exists
  $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt->close();
    header("Location: manage_users.php?error=duplicate");
    exit();
  }
  $stmt->close();

  // Check if email already exists
  $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt->close();
    header("Location: manage_users.php?error=duplicate");
    exit();
  }
  $stmt->close();

  // Hash password
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  // Insert new user
  $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, email, full_name, is_active) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssi", $username, $password_hash, $email, $full_name, $is_active);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_users.php?success=added");
    exit();
  } else {
    $stmt->close();
    header("Location: manage_users.php?error=database");
    exit();
  }
}

/**
 * Edit existing admin user
 */
function editUser($conn)
{
  // Validate required fields
  if (
    !isset($_POST['user_id']) || !isset($_POST['username']) ||
    !isset($_POST['email']) || !isset($_POST['full_name'])
  ) {
    header("Location: manage_users.php?error=missing_fields");
    exit();
  }

  $user_id = intval($_POST['user_id']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $full_name = trim($_POST['full_name']);
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

  // Validate user ID
  if ($user_id <= 0) {
    header("Location: manage_users.php?error=invalid_id");
    exit();
  }

  // Validate username format
  if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    header("Location: manage_users.php?error=invalid_username");
    exit();
  }

  // Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: manage_users.php?error=invalid_email");
    exit();
  }

  // If password is being changed, validate it
  if (!empty($password)) {
    // Validate password length
    if (strlen($password) < 8) {
      header("Location: manage_users.php?error=password_too_short");
      exit();
    }

    // Check if passwords match
    if ($password !== $password_confirm) {
      header("Location: manage_users.php?error=password_mismatch");
      exit();
    }
  }

  // Check if username already exists for a different user
  $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
  $stmt->bind_param("si", $username, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt->close();
    header("Location: manage_users.php?error=duplicate");
    exit();
  }
  $stmt->close();

  // Check if email already exists for a different user
  $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
  $stmt->bind_param("si", $email, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt->close();
    header("Location: manage_users.php?error=duplicate");
    exit();
  }
  $stmt->close();

  // Update user
  if (!empty($password)) {
    // Update with new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, password_hash = ?, email = ?, full_name = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $password_hash, $email, $full_name, $user_id);
  } else {
    // Update without changing password
    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, full_name = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $full_name, $user_id);
  }

  if ($stmt->execute()) {
    $stmt->close();

    // If the user edited their own account, update session variables
    if ($user_id == $_SESSION['admin_id']) {
      $_SESSION['admin_username'] = $username;
      $_SESSION['admin_fullname'] = $full_name;
      $_SESSION['admin_email'] = $email;
    }

    header("Location: manage_users.php?success=updated");
    exit();
  } else {
    $stmt->close();
    header("Location: manage_users.php?error=database");
    exit();
  }
}

/**
 * Delete admin user
 */
function deleteUser($conn)
{
  // Validate required fields
  if (!isset($_POST['user_id'])) {
    header("Location: manage_users.php?error=missing_fields");
    exit();
  }

  $user_id = intval($_POST['user_id']);

  // Validate user ID
  if ($user_id <= 0) {
    header("Location: manage_users.php?error=invalid_id");
    exit();
  }

  // Prevent deleting own account
  if ($user_id == $_SESSION['admin_id']) {
    header("Location: manage_users.php?error=delete_self");
    exit();
  }

  // Delete user
  $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
  $stmt->bind_param("i", $user_id);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_users.php?success=deleted");
    exit();
  } else {
    $stmt->close();
    header("Location: manage_users.php?error=database");
    exit();
  }
}

/**
 * Toggle user active status
 */
function toggleStatus($conn)
{
  // Validate required fields
  if (!isset($_POST['user_id']) || !isset($_POST['current_status'])) {
    header("Location: manage_users.php?error=missing_fields");
    exit();
  }

  $user_id = intval($_POST['user_id']);
  $current_status = intval($_POST['current_status']);

  // Validate user ID
  if ($user_id <= 0) {
    header("Location: manage_users.php?error=invalid_id");
    exit();
  }

  // Prevent deactivating own account
  if ($user_id == $_SESSION['admin_id']) {
    header("Location: manage_users.php?error=cannot_deactivate_self");
    exit();
  }

  // Toggle status
  $new_status = $current_status ? 0 : 1;

  $stmt = $conn->prepare("UPDATE admin_users SET is_active = ? WHERE id = ?");
  $stmt->bind_param("ii", $new_status, $user_id);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_users.php?success=status_updated");
    exit();
  } else {
    $stmt->close();
    header("Location: manage_users.php?error=database");
    exit();
  }
}
?>