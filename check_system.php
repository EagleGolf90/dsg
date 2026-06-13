<?php
/**
 * Quick System Status Check
 * Run this file directly in your browser to see the system status
 * Example: http://yourdomain.com/check_system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration System Status</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      overflow: hidden;
    }

    .header {
      background: #2d3748;
      color: white;
      padding: 30px;
      text-align: center;
    }

    .header h1 {
      font-size: 28px;
      margin-bottom: 10px;
    }

    .header p {
      opacity: 0.8;
    }

    .content {
      padding: 30px;
    }

    .check-item {
      background: #f7fafc;
      border-left: 4px solid #cbd5e0;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    .check-item.success {
      border-left-color: #48bb78;
      background: #f0fff4;
    }

    .check-item.error {
      border-left-color: #f56565;
      background: #fff5f5;
    }

    .check-item.warning {
      border-left-color: #ed8936;
      background: #fffaf0;
    }

    .check-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
    }

    .icon {
      display: inline-block;
      width: 24px;
      height: 24px;
      margin-right: 10px;
      text-align: center;
      font-size: 18px;
    }

    .success .icon {
      color: #48bb78;
    }

    .error .icon {
      color: #f56565;
    }

    .warning .icon {
      color: #ed8936;
    }

    .check-details {
      color: #4a5568;
      line-height: 1.6;
      margin-top: 10px;
    }

    .check-details code {
      background: #edf2f7;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 13px;
    }

    .action-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 16px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 14px;
    }

    .action-btn:hover {
      background: #5568d3;
    }

    .summary {
      background: #edf2f7;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 30px;
    }

    .summary-item {
      display: inline-block;
      margin-right: 30px;
      font-size: 14px;
    }

    .summary-item strong {
      font-size: 24px;
      display: block;
    }

    .log-viewer {
      background: #1a202c;
      color: #68d391;
      padding: 15px;
      border-radius: 5px;
      font-family: 'Courier New', monospace;
      font-size: 12px;
      max-height: 300px;
      overflow-y: auto;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>🔧 Registration System Status</h1>
      <p>Diagnostic check performed on <?php echo date('F j, Y g:i A'); ?></p>
    </div>
    <div class="content">
      <?php
      $checks = [];
      $successCount = 0;
      $errorCount = 0;
      $warningCount = 0;

      // Check 1: Configuration File
      $configPath = __DIR__ . '/includes/config.php';
      if (file_exists($configPath)) {
        require_once $configPath;
        $checks[] = [
          'status' => 'success',
          'title' => 'Configuration File',
          'message' => 'Config file loaded successfully',
          'details' => "File: <code>includes/config.php</code>"
        ];
        $successCount++;
      } else {
        $checks[] = [
          'status' => 'error',
          'title' => 'Configuration File Missing',
          'message' => 'Could not find configuration file',
          'details' => "Expected location: <code>$configPath</code>",
          'action' => 'Create the config.php file in the includes/ directory'
        ];
        $errorCount++;
      }

      // Check 2: Database Connection
      if (isset($db_host, $db_username, $db_password, $db_name)) {
        try {
          $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

          if ($conn->connect_error) {
            $checks[] = [
              'status' => 'error',
              'title' => 'Database Connection Failed',
              'message' => $conn->connect_error,
              'details' => "Host: <code>$db_host</code><br>Database: <code>$db_name</code>",
              'action' => 'Verify database credentials in config.php and ensure database server is accessible'
            ];
            $errorCount++;
          } else {
            $checks[] = [
              'status' => 'success',
              'title' => 'Database Connection',
              'message' => 'Successfully connected to database',
              'details' => "Server: <code>$db_host</code><br>Database: <code>$db_name</code><br>Version: {$conn->server_info}"
            ];
            $successCount++;

            // Check 3: Database Tables
            $requiredTables = ['registrations', 'registrations_others'];
            $missingTables = [];

            foreach ($requiredTables as $table) {
              $result = $conn->query("SHOW TABLES LIKE '$table'");
              if (!$result || $result->num_rows === 0) {
                $missingTables[] = $table;
              }
            }

            if (empty($missingTables)) {
              $checks[] = [
                'status' => 'success',
                'title' => 'Database Tables',
                'message' => 'All required tables exist',
                'details' => "Tables: <code>registrations</code>, <code>registrations_others</code>"
              ];
              $successCount++;
            } else {
              $checks[] = [
                'status' => 'error',
                'title' => 'Missing Database Tables',
                'message' => 'Some required tables are missing',
                'details' => "Missing: <code>" . implode('</code>, <code>', $missingTables) . "</code>",
                'action' => 'Run the database_setup.sql script in your database'
              ];
              $errorCount++;
            }

            $conn->close();
          }
        } catch (Exception $e) {
          $checks[] = [
            'status' => 'error',
            'title' => 'Database Error',
            'message' => $e->getMessage(),
            'action' => 'Check database configuration and server status'
          ];
          $errorCount++;
        }
      }

      // Check 4: PHPMailer
      $phpmailerPath = __DIR__ . '/mail/PHPMailer/src/PHPMailer.php';
      if (file_exists($phpmailerPath)) {
        require_once __DIR__ . '/mail/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/mail/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/mail/PHPMailer/src/SMTP.php';

        $checks[] = [
          'status' => 'success',
          'title' => 'PHPMailer Library',
          'message' => 'PHPMailer is installed',
          'details' => "Version: " . PHPMailer\PHPMailer\PHPMailer::VERSION
        ];
        $successCount++;
      } else {
        $checks[] = [
          'status' => 'warning',
          'title' => 'PHPMailer Not Found',
          'message' => 'Email functionality may not work',
          'details' => "Expected: <code>mail/PHPMailer/src/PHPMailer.php</code>",
          'action' => 'Install PHPMailer or verify the path'
        ];
        $warningCount++;
      }

      // Check 5: PHP Version & Extensions
      $phpVersion = phpversion();
      $phpOk = version_compare($phpVersion, '7.0.0', '>=');

      if ($phpOk) {
        $checks[] = [
          'status' => 'success',
          'title' => 'PHP Version',
          'message' => "PHP $phpVersion",
          'details' => "Minimum requirement: PHP 7.0+"
        ];
        $successCount++;
      } else {
        $checks[] = [
          'status' => 'error',
          'title' => 'PHP Version Too Old',
          'message' => "PHP $phpVersion is outdated",
          'details' => "Minimum requirement: PHP 7.0+",
          'action' => 'Upgrade PHP to version 7.0 or higher'
        ];
        $errorCount++;
      }

      // Check extensions
      $requiredExtensions = ['mysqli', 'json'];
      $missingExtensions = [];
      foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
          $missingExtensions[] = $ext;
        }
      }

      if (empty($missingExtensions)) {
        $checks[] = [
          'status' => 'success',
          'title' => 'PHP Extensions',
          'message' => 'All required extensions are loaded',
          'details' => "Extensions: <code>" . implode('</code>, <code>', $requiredExtensions) . "</code>"
        ];
        $successCount++;
      } else {
        $checks[] = [
          'status' => 'error',
          'title' => 'Missing PHP Extensions',
          'message' => 'Some required extensions are not loaded',
          'details' => "Missing: <code>" . implode('</code>, <code>', $missingExtensions) . "</code>",
          'action' => 'Enable missing extensions in php.ini'
        ];
        $errorCount++;
      }

      // Check 6: Error Log
      $logFile = __DIR__ . '/testing/registration_errors.log';
      $logExists = file_exists($logFile);
      $logSize = $logExists ? filesize($logFile) : 0;
      $logContent = '';

      if ($logExists && $logSize > 0) {
        // Read last 2000 characters of log
        $handle = fopen($logFile, 'r');
        if ($logSize > 2000) {
          fseek($handle, -2000, SEEK_END);
          fgets($handle); // Skip partial line
        }
        $logContent = fread($handle, 2000);
        fclose($handle);

        $checks[] = [
          'status' => 'warning',
          'title' => 'Error Log Exists',
          'message' => 'Recent errors have been logged',
          'details' => "Log file: <code>testing/registration_errors.log</code><br>Size: " . number_format($logSize) . " bytes",
          'action' => 'Review the log below for details'
        ];
        $warningCount++;
      } else {
        $checks[] = [
          'status' => 'success',
          'title' => 'Error Log',
          'message' => 'No errors logged yet',
          'details' => "Log will be created automatically when errors occur"
        ];
        $successCount++;
      }

      // Display summary
      ?>
      <div class="summary">
        <div class="summary-item">
          <strong style="color: #48bb78;"><?php echo $successCount; ?></strong>
          Passed
        </div>
        <div class="summary-item">
          <strong style="color: #f56565;"><?php echo $errorCount; ?></strong>
          Errors
        </div>
        <div class="summary-item">
          <strong style="color: #ed8936;"><?php echo $warningCount; ?></strong>
          Warnings
        </div>
      </div>

      <?php
      // Display all checks
      foreach ($checks as $check) {
        $statusClass = $check['status'];
        $icon = $statusClass === 'success' ? '✓' : ($statusClass === 'error' ? '✗' : '⚠');

        echo "<div class='check-item $statusClass'>";
        echo "<div class='check-title'>";
        echo "<span class='icon'>$icon</span>";
        echo htmlspecialchars($check['title']);
        echo "</div>";
        echo "<div class='check-details'>";
        echo "<strong>" . htmlspecialchars($check['message']) . "</strong><br>";
        if (!empty($check['details'])) {
          echo $check['details'] . "<br>";
        }
        if (!empty($check['action'])) {
          echo "<br><strong>Action Required:</strong> " . htmlspecialchars($check['action']);
        }
        echo "</div>";
        echo "</div>";
      }

      // Display log if exists
      if (!empty($logContent)) {
        echo "<h2 style='margin-top: 30px; margin-bottom: 15px;'>Recent Error Log</h2>";
        echo "<div class='log-viewer'>";
        echo htmlspecialchars($logContent);
        echo "</div>";
      }
      ?>

      <div style="margin-top: 30px; padding: 20px; background: #edf2f7; border-radius: 5px;">
        <h3 style="margin-bottom: 10px;">Next Steps</h3>
        <?php if ($errorCount > 0): ?>
          <p>⚠️ <strong>There are <?php echo $errorCount; ?> error(s) that need to be fixed before the registration system
              will work properly.</strong></p>
          <p>Please address the errors listed above, then refresh this page to re-check.</p>
        <?php elseif ($warningCount > 0): ?>
          <p>✓ System is mostly ready, but there are <?php echo $warningCount; ?> warning(s) to review.</p>
          <p>The registration system should work, but check the warnings for potential issues.</p>
        <?php else: ?>
          <p style="color: #48bb78;">✓ <strong>All checks passed! Your registration system is ready to use.</strong></p>
          <p>Try submitting a test registration to verify everything works end-to-end.</p>
        <?php endif; ?>

        <div style="margin-top: 15px;">
          <a href="registration.html" class="action-btn">Go to Registration Form</a>
          <a href="testing/debug_registration.php" class="action-btn">Run Detailed Diagnostics</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>