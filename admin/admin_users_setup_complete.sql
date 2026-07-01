-- ============================================================
-- Admin Users Table Setup - COMPLETE VERSION
-- This creates the admin_users table and helps set up a default admin account
-- ============================================================

-- Create admin_users table
CREATE TABLE
IF NOT EXISTS admin_users
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR
(50) UNIQUE NOT NULL,
    password_hash VARCHAR
(255) NOT NULL,
    email VARCHAR
(100) NOT NULL,
    full_name VARCHAR
(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT
(1) DEFAULT 1,
    INDEX idx_username
(username),
    INDEX idx_email
(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- IMPORTANT: GENERATE PASSWORD HASH
-- ============================================================
-- To create your first admin user, you need to generate a password hash.
-- 
-- METHOD 1: Use the generate_hash.php script
-- 1. Create a file called generate_hash.php in your admin folder
-- 2. Copy this code into it:
--
-- <?php
-- $password = 'YourDesiredPassword123!';
-- $hash = password_hash($password, PASSWORD_DEFAULT);
-- echo "Password Hash: " . $hash;
-- ?>
--
-- 3. Run it in your browser: http://yoursite.com/admin/generate_hash.php
-- 4. Copy the hash and use it in the INSERT statement below
-- 5. DELETE the generate_hash.php file after use (security!)
--
-- METHOD 2: Use online password generator (less secure)
-- Use a bcrypt hash generator online (not recommended for production)
-- ============================================================

-- Insert default admin user
-- REPLACE THE VALUES BELOW WITH YOUR DESIRED VALUES:
-- - Change 'admin' to your desired username
-- - Replace the hash with your generated password hash
-- - Change the email to your email
-- - Change the full name to your name

INSERT INTO admin_users
  (username, password_hash, email, full_name, is_active)
VALUES
  (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is hash for 'password'
    'admin@example.com',
    'System Administrator',
    1
)
ON DUPLICATE KEY
UPDATE username=username;

-- ============================================================
-- AFTER INSTALLATION
-- ============================================================
-- 1. Login with your credentials
-- 2. IMMEDIATELY change your password through the admin panel
-- 3. Navigate to: Admin Dashboard > Manage Admin Users > Edit your account
-- 4. Set a new secure password
-- 5. Consider creating separate admin accounts for each administrator
-- ============================================================

-- ============================================================
-- SECURITY NOTES
-- ============================================================
-- - Never commit actual password hashes to version control
-- - Always use strong passwords (min 8 characters, mixed case, numbers, symbols)
-- - Delete any password hash generation scripts after use
-- - Regularly review and remove unused admin accounts
-- - Keep this file secure and don't expose it publicly
-- ============================================================
