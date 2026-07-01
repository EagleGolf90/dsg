<?php
/**
 * Password Hash Generator
 * 
 * SECURITY WARNING:
 * This file should ONLY be used during initial setup to generate password hashes.
 * DELETE this file immediately after generating your password hash!
 * 
 * Usage:
 * 1. Open this file in a web browser
 * 2. Enter your desired password
 * 3. Copy the generated hash
 * 4. Use the hash in your SQL INSERT statement
 * 5. DELETE THIS FILE!
 */

$generated_hash = '';
$password_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
  $password_input = $_POST['password'];

  if (!empty($password_input)) {
    // Generate the hash
    $generated_hash = password_hash($password_input, PASSWORD_DEFAULT);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Hash Generator</title>
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

    .container {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      max-width: 600px;
      width: 100%;
    }

    .warning {
      background: #fff3cd;
      border: 2px solid #ffc107;
      border-radius: 5px;
      padding: 20px;
      margin-bottom: 30px;
      color: #856404;
    }

    .warning h2 {
      color: #d32f2f;
      margin-bottom: 10px;
      font-size: 20px;
    }

    .warning p {
      line-height: 1.6;
      margin-bottom: 10px;
    }

    .warning strong {
      color: #d32f2f;
    }

    h1 {
      color: #333;
      margin-bottom: 20px;
      text-align: center;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-weight: 600;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
    }

    .form-group input:focus {
      outline: none;
      border-color: #667eea;
    }

    .form-group small {
      display: block;
      margin-top: 5px;
      color: #999;
      font-size: 12px;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: opacity 0.3s;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .result {
      margin-top: 30px;
      padding: 20px;
      background: #f5f7fa;
      border-radius: 5px;
      border: 1px solid #dee2e6;
    }

    .result h3 {
      color: #333;
      margin-bottom: 15px;
    }

    .hash-output {
      background: white;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      word-break: break-all;
      font-family: 'Courier New', monospace;
      font-size: 14px;
      color: #333;
      margin-bottom: 15px;
      max-height: 150px;
      overflow-y: auto;
    }

    .copy-btn {
      padding: 8px 16px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s;
    }

    .copy-btn:hover {
      background: #218838;
    }

    .instructions {
      margin-top: 20px;
      padding: 15px;
      background: #e3f2fd;
      border-radius: 5px;
      color: #0d47a1;
    }

    .instructions h4 {
      margin-bottom: 10px;
    }

    .instructions ol {
      margin-left: 20px;
      line-height: 1.8;
    }

    .delete-reminder {
      margin-top: 20px;
      padding: 15px;
      background: #ffebee;
      border: 2px solid #f44336;
      border-radius: 5px;
      color: #b71c1c;
      font-weight: bold;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="warning">
      <h2>⚠️ SECURITY WARNING</h2>
      <p><strong>This file is for INITIAL SETUP ONLY!</strong></p>
      <p>After generating your password hash:</p>
      <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Copy the generated hash</li>
        <li>Use it in your SQL setup</li>
        <li><strong>DELETE THIS FILE IMMEDIATELY!</strong></li>
      </ul>
      <p style="margin-top: 10px;">Leaving this file accessible is a serious security risk!</p>
    </div>

    <h1>🔐 Password Hash Generator</h1>

    <form method="POST">
      <div class="form-group">
        <label for="password">Enter Password:</label>
        <input type="password" id="password" name="password" required minlength="8"
          value="<?php echo htmlspecialchars($password_input); ?>">
        <small>Minimum 8 characters recommended. Use a strong password with mixed case, numbers, and symbols.</small>
      </div>

      <button type="submit" class="btn">Generate Hash</button>
    </form>

    <?php if ($generated_hash): ?>
      <div class="result">
        <h3>✅ Generated Password Hash:</h3>
        <div class="hash-output" id="hashOutput"><?php echo htmlspecialchars($generated_hash); ?></div>
        <button class="copy-btn" onclick="copyHash()">📋 Copy to Clipboard</button>

        <div class="instructions">
          <h4>Next Steps:</h4>
          <ol>
            <li>Copy the hash above (click the button)</li>
            <li>Open your <code>admin_users_setup_complete.sql</code> file</li>
            <li>Replace the password_hash value with this hash</li>
            <li>Run the SQL script to create your admin user</li>
            <li><strong style="color: #d32f2f;">DELETE THIS FILE (generate_hash.php)</strong></li>
          </ol>
        </div>

        <div class="delete-reminder">
          🚨 Remember to DELETE this file after use! 🚨
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function copyHash() {
      const hashText = document.getElementById('hashOutput').textContent;
      navigator.clipboard.writeText(hashText).then(function () {
        alert('✅ Hash copied to clipboard!');
      }, function (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = hashText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('✅ Hash copied to clipboard!');
      });
    }

    // Show warning on page load
    window.onload = function () {
      if (confirm('⚠️ SECURITY REMINDER:\n\nThis file should be deleted after use!\n\nDo you understand that leaving this file accessible is a security risk?\n\nClick OK to continue, Cancel to close this page.')) {
        // User acknowledged
      } else {
        window.close();
      }
    }
  </script>
</body>

</html>