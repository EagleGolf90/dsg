<?php
/**
 * Admin User Setup Script
 * Run this file once to create the admin_users table and default admin account
 * Access: http://yourdomain.com/admin/setup_admin.php
 * 
 * SECURITY: Delete this file after running it successfully!
 */

// Include database configuration
require_once '../includes/config.php';

// Create database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Admin User Setup</h2>";

// Step 1: Create admin_users table
$create_table_sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($create_table_sql) === TRUE) {
  echo "<p>✓ Table 'admin_users' created successfully or already exists.</p>";
} else {
  echo "<p>✗ Error creating table: " . $conn->error . "</p>";
}

// Step 2: Create default admin user
$default_username = 'admin';
$default_password = 'Admin@123'; // Default password - MUST BE CHANGED!
$default_email = 'admin@dsg.com';
$default_fullname = 'System Administrator';

// Generate password hash
$password_hash = password_hash($default_password, PASSWORD_DEFAULT);

// Check if admin user already exists
$check_sql = "SELECT id FROM admin_users WHERE username = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $default_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  echo "<p>⚠ Admin user already exists. Skipping user creation.</p>";
} else {
  // Insert admin user
  $insert_sql = "INSERT INTO admin_users (username, password_hash, email, full_name) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($insert_sql);
  $stmt->bind_param("ssss", $default_username, $password_hash, $default_email, $default_fullname);

  if ($stmt->execute()) {
    echo "<p>✓ Default admin user created successfully!</p>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 20px 0;'>";
    echo "<strong>Default Login Credentials:</strong><br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>Admin@123</strong><br><br>";
    echo "<strong style='color: red;'>⚠ IMPORTANT: Change this password immediately after first login!</strong>";
    echo "</div>";
  } else {
    echo "<p>✗ Error creating admin user: " . $conn->error . "</p>";
  }
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<p><strong>Setup Complete!</strong></p>";
echo "<p>Next steps:</p>";
echo "<ol>";
echo "<li>Delete this file (setup_admin.php) for security</li>";
echo "<li><a href='login.php'>Go to Admin Login</a></li>";
echo "<li>Login with the default credentials shown above</li>";
echo "<li>Change your password immediately</li>";
echo "</ol>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Setup Complete</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 800px;
      margin: 50px auto;
      padding: 20px;
      background: #f5f5f5;
    }

    h2 {
      color: #333;
    }

    p {
      line-height: 1.6;
    }
  </style>
</head>

<body>
</body>

</html>