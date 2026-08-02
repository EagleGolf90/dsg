<?php
/**
 * Improved Registration Handler with Enhanced Error Logging
 * This version includes detailed error logging to help debug issues
 */

// Disable display errors to prevent breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom error logging function
function logRegistrationError($message, $data = [])
{
  $log_dir = __DIR__ . '/testing';
  $log_file = $log_dir . '/registration_errors.log';

  // Create log directory if it doesn't exist
  if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
  }

  $timestamp = date('Y-m-d H:i:s');
  $log_message = "[$timestamp] $message\n";
  if (!empty($data)) {
    $log_message .= "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
  }
  $log_message .= str_repeat('-', 80) . "\n";

  // Use file_put_contents instead of error_log for better control
  @file_put_contents($log_file, $log_message, FILE_APPEND);
}

header('Content-Type: application/json');

// Include configuration
try {
  require_once 'includes/config.php';
} catch (Exception $e) {
  logRegistrationError('Failed to load config.php', ['error' => $e->getMessage()]);
  echo json_encode(['success' => false, 'message' => 'Configuration error. Please contact support.']);
  exit;
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
  require 'mail/PHPMailer/src/Exception.php';
  require 'mail/PHPMailer/src/PHPMailer.php';
  require 'mail/PHPMailer/src/SMTP.php';
} catch (Exception $e) {
  logRegistrationError('Failed to load PHPMailer', ['error' => $e->getMessage()]);
  echo json_encode(['success' => false, 'message' => 'Email system error. Please contact support.']);
  exit;
}

// Get JSON input - handle both AJAX and regular form submissions
$input = file_get_contents('php://input');
logRegistrationError('Registration attempt received', ['raw_input_length' => strlen($input)]);

// Try to get data from JSON input first (AJAX submission)
$data = json_decode($input, true);

// If JSON input is empty or invalid, try to get data from $_POST (regular form submission)
if (!$data && !empty($_POST)) {
  logRegistrationError('Received regular POST data instead of JSON, converting...', ['POST_keys' => array_keys($_POST)]);

  // Convert regular POST data to expected format
  $data = [
    'primaryRegistrant' => [
      'firstName' => $_POST['firstName'] ?? '',
      'lastName' => $_POST['lastName'] ?? '',
      'email' => $_POST['email'] ?? '',
      'cellPhone' => $_POST['cellPhone'] ?? ''
    ],
    'banquetAttendees' => intval($_POST['banquetAttendees'] ?? 1),
    'additionalAttendees' => [],
    'submittedAt' => date('c')
  ];

  // Collect additional attendees from POST data
  $i = 1;
  while (isset($_POST["additionalFirstName$i"]) && isset($_POST["additionalLastName$i"])) {
    $data['additionalAttendees'][] = [
      'firstName' => $_POST["additionalFirstName$i"],
      'lastName' => $_POST["additionalLastName$i"]
    ];
    $i++;
  }
}

// Validate input
if (!$data) {
  $json_error = json_last_error_msg();
  logRegistrationError('Invalid input - no JSON and no POST data', [
    'json_error' => $json_error,
    'raw_input' => substr($input, 0, 500),
    'POST_data' => $_POST
  ]);
  echo json_encode(['success' => false, 'message' => 'Invalid input data. Please try again.']);
  exit;
}

// Log sanitized registration data
logRegistrationError('Processing registration', [
  'email' => $data['primaryRegistrant']['email'] ?? 'N/A',
  'name' => ($data['primaryRegistrant']['firstName'] ?? '') . ' ' . ($data['primaryRegistrant']['lastName'] ?? ''),
  'attendees' => $data['banquetAttendees'] ?? 0
]);

try {
  // Create database connection
  $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

  // Check connection
  if ($conn->connect_error) {
    logRegistrationError('Database connection failed', [
      'error' => $conn->connect_error,
      'host' => $db_host,
      'database' => $db_name
    ]);
    throw new Exception("Unable to connect to database. Please try again later.");
  }

  logRegistrationError('Database connected successfully');

  // Validate required fields
  $required_fields = ['firstName', 'lastName', 'email', 'cellPhone'];
  foreach ($required_fields as $field) {
    if (!isset($data['primaryRegistrant'][$field]) || empty($data['primaryRegistrant'][$field])) {
      logRegistrationError("Missing required field: $field");
      throw new Exception("Missing required field: $field");
    }
  }

  if (!isset($data['banquetAttendees']) || $data['banquetAttendees'] < 1) {
    logRegistrationError('Invalid banquet attendees count');
    throw new Exception("Invalid number of attendees");
  }

  // Use prepared statements to prevent SQL injection
  $stmt = $conn->prepare("INSERT INTO registrations (first_name, last_name, email, cell_phone, banquet_attendees, total_cost, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

  if (!$stmt) {
    logRegistrationError('Failed to prepare statement', ['error' => $conn->error]);
    throw new Exception("Database error. Please try again.");
  }

  $firstName = $data['primaryRegistrant']['firstName'];
  $lastName = $data['primaryRegistrant']['lastName'];
  $email = $data['primaryRegistrant']['email'];
  $cellPhone = $data['primaryRegistrant']['cellPhone'];
  $banquetAttendees = intval($data['banquetAttendees']);
  $totalCost = $banquetAttendees * 55.00;
  $submittedAt = date('Y-m-d H:i:s'); // Use current time for consistency

  $stmt->bind_param("ssssdss", $firstName, $lastName, $email, $cellPhone, $banquetAttendees, $totalCost, $submittedAt);

  if (!$stmt->execute()) {
    logRegistrationError('Failed to insert registration', ['error' => $stmt->error]);
    throw new Exception("Failed to save registration. Please try again.");
  }

  // Get the inserted registration ID
  $registrationId = $stmt->insert_id;
  $stmt->close();

  logRegistrationError('Registration inserted successfully', ['registration_id' => $registrationId]);

  // Insert additional attendees if any
  if (isset($data['additionalAttendees']) && is_array($data['additionalAttendees']) && count($data['additionalAttendees']) > 0) {
    $stmt = $conn->prepare("INSERT INTO registrations_others (registration_id, first_name, last_name) VALUES (?, ?, ?)");

    if (!$stmt) {
      logRegistrationError('Failed to prepare statement for additional attendees', ['error' => $conn->error]);
      // Don't fail the registration, just log it
    } else {
      foreach ($data['additionalAttendees'] as $index => $attendee) {
        $addFirstName = $attendee['firstName'];
        $addLastName = $attendee['lastName'];

        $stmt->bind_param("iss", $registrationId, $addFirstName, $addLastName);

        if (!$stmt->execute()) {
          logRegistrationError('Failed to insert additional attendee', [
            'index' => $index,
            'error' => $stmt->error
          ]);
        }
      }
      $stmt->close();
      logRegistrationError('Additional attendees processed', ['count' => count($data['additionalAttendees'])]);
    }
  }

  // Close database connection
  $conn->close();

  // Send email notification
  $mail = new PHPMailer(true);

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'mail.fulltimberstack.dev';
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@fulltimberstack.dev';
    $mail->Password = '!mNmhbPk9hKBKAEB';
    $mail->Port = 25;
    $mail->SMTPSecure = false;
    $mail->SMTPAutoTLS = false;

    // Sender & recipient
    $mail->setFrom('admin@fulltimberstack.dev', 'DSG Registration System');
    $mail->addAddress('ssadsc@msn.com');
    $mail->addAddress('cjs1204@gmail.com');
    $mail->addAddress('dsgtreasurer1@gmail.com');
    $mail->addAddress($email, "$firstName $lastName");

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "DSG Registration: Christmas Luncheon - $firstName $lastName";

    // Build additional attendees list for email
    $additionalAttendeesHtml = '';
    if (isset($data['additionalAttendees']) && count($data['additionalAttendees']) > 0) {
      $additionalAttendeesHtml = '<h3>Additional Attendees:</h3><ul>';
      $counter = 2;
      foreach ($data['additionalAttendees'] as $attendee) {
        $additionalAttendeesHtml .= "<li>$counter. {$attendee['firstName']} {$attendee['lastName']}</li>";
        $counter++;
      }
      $additionalAttendeesHtml .= '</ul>';
    } else {
      $additionalAttendeesHtml = '<p><em>No additional attendees</em></p>';
    }

    // Format submitted date
    $submittedDate = date('F j, Y, g:i a', strtotime($submittedAt));

    $CashApp = '$Swooshdeaf';

    $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
                    .label { font-weight: bold; color: #667eea; }
                    .separator { border-top: 2px solid #667eea; margin: 20px 0; }
                    ul { list-style-type: none; padding-left: 0; }
                    ul li { padding: 5px 0; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>DSG Christmas Luncheon Registration</h1>
                </div>
                <div class='content'>
                    <div class='info-box'>
                        <h2>Primary Registrant</h2>
                        <p><span class='label'>Name:</span> $firstName $lastName</p>
                        <p><span class='label'>Email:</span> $email</p>
                        <p><span class='label'>Phone:</span> $cellPhone</p>
                    </div>
                    
                    <div class='info-box'>
                        <h2>Event Information</h2>
                        <p><span class='label'>Total Attendees:</span> $banquetAttendees</p>
                        <p><span class='label'>Total Cost:</span> $" . number_format($totalCost, 2) . "</p>
                    </div>
                    
                    <div class='info-box'>
                        $additionalAttendeesHtml
                    </div>
                    
                    <div class='separator'></div>
                    
                    <p><span class='label'>Submitted:</span> $submittedDate</p>
                    <p><span class='label'>Registration ID:</span> #$registrationId</p>
                    
                    <p style='margin-top: 30px; color: #666; font-size: 0.9em;'>
                        <em>This is an automated notification from the DSG Christmas Luncheon registration system.</em>
                    </p>

                    <p style='margin-top: 30px; color: #666; font-size: 0.9em;'>
                        <em>
                        To one of the payment methods you choose, make sure to put Payable to: <b>DSG</b>
                        For check, mail to:<br/>Kelly Brady<br/>P.O. Box 3618<br/>Lilburn, GA 30048<br/><br/>Payable to: <b>DSG</b><br/><br/>
                        For CashApp, UserName: $CashApp<br/>
                        For Zelle, use email: dsgtreasurer1@gmail.com<br/>
                        For Zeffy's, use email: deafseniorsofgeorgia.org<br/>
                        </em>
                    </p>
                </div>
            </body>
            </html>
            ";

    // Plain text alternative
    $mail->AltBody = "New Christmas Luncheon Registration\n\n" .
      "Primary Registrant:\n" .
      "Name: $firstName $lastName\n" .
      "Email: $email\n" .
      "Phone: $cellPhone\n\n" .
      "Event Information:\n" .
      "Total Attendees: $banquetAttendees\n" .
      "Total Cost: $" . number_format($totalCost, 2) . "\n\n" .
      "Submitted: $submittedDate\n" .
      "Registration ID: #$registrationId";

    $mail->send();
    logRegistrationError('Email sent successfully', ['registration_id' => $registrationId]);

  } catch (Exception $e) {
    // Log email error but don't fail the registration
    logRegistrationError('Email sending failed', [
      'error' => $mail->ErrorInfo,
      'exception' => $e->getMessage(),
      'registration_id' => $registrationId
    ]);
  }

  // Return success response
  logRegistrationError('Registration completed successfully', ['registration_id' => $registrationId]);
  echo json_encode([
    'success' => true,
    'message' => 'Registration successful',
    'registrationId' => $registrationId
  ]);

} catch (Exception $e) {
  // Log the error
  logRegistrationError('Registration failed with exception', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
  ]);

  // Return error response
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>