-- ============================================================
-- Admin Users Table Setup
-- This creates the admin_users table and a default admin account
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

-- Insert default admin user
-- Username: admin
-- Password: Admin@123
-- IMPORTANT: Change this password immediately after first login!
INSERT INTO admin_users
  (username, password_hash, email, full_name)
VALUES
  (
    'admin',
    '$2y$10$YourHashedPasswordHere',
    'admin@example.com',
    'System Administrator'
)
ON DUPLICATE KEY
UPDATE username=username;

-- Note: You need to generate the password hash using PHP
-- Run this PHP code to generate the hash:
-- <?php echo password_hash('Admin@123', PASSWORD_DEFAULT); ?>
-- Then replace '$2y$10$YourHashedPasswordHere' with the generated hash
