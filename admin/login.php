<?php
/**
 * Admin Login Page
 * Handles admin authentication
 */

// Start session
session_start();

// If already logged in, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Include database configuration
require_once '../includes/config.php';

$error_message = '';
$success_message = '';

// Check for timeout parameter
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_message = 'Your session has timed out. Please login again.';
}

// Check for logout parameter
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $success_message = 'You have been successfully logged out.';
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Create database connection
        $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
        
        // Check connection
        if ($conn->connect_error) {
            $error_message = 'Database connection failed. Please try again later.';
        } else {
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password_hash, email, full_name, is_active FROM admin_users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Check if account is active
                if ($user['is_active'] != 1) {
                    $error_message = 'Your account has been deactivated. Please contact the administrator.';
                } else {
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Password is correct, create session
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_email'] = $user['email'];
                        $_SESSION['admin_fullname'] = $user['full_name'];
                        $_SESSION['last_activity'] = time();
                        $_SESSION['created'] = time();
                        
                        // Update last login timestamp
                        $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                        $update_stmt->bind_param("i", $user['id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error_message = 'Invalid username or password.';
                    }
                }
            } else {
                $error_message = 'Invalid username or password.';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DSG</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        .security-note {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Admin Login</h1>
            <p>Sign in to access the admin panel</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" name="login" class="btn-login">
                Login
            </button>
        </form>

        <div class="back-home">
            <a href="../index.html">← Back to Home</a>
        </div>

        <div class="security-note">
            <strong>🛡️ Security Notice:</strong><br>
            This area is restricted to authorized personnel only. All login attempts are logged.
        </div>
    </div>
</body>

</html>
