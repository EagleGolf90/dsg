<?php
// Disable display errors to prevent breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Include configuration
require_once 'includes/config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mail/PHPMailer/src/Exception.php';
require 'mail/PHPMailer/src/PHPMailer.php';
require 'mail/PHPMailer/src/SMTP.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data) {
  echo json_encode(['success' => false, 'message' => 'Invalid input data']);
  exit;
}

try {
  // Create database connection
  $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

  // Check connection
  if ($conn->connect_error) {
    throw new Exception("Database connection failed: " . $conn->connect_error);
  }

  // Extract primary registrant data
  $firstName = $conn->real_escape_string($data['primaryRegistrant']['firstName']);
  $lastName = $conn->real_escape_string($data['primaryRegistrant']['lastName']);
  $email = $conn->real_escape_string($data['primaryRegistrant']['email']);
  $cellPhone = $conn->real_escape_string($data['primaryRegistrant']['cellPhone']);
  $banquetAttendees = intval($data['banquetAttendees']);
  $totalCost = $banquetAttendees * 55.00;
  $submittedAt = $conn->real_escape_string($data['submittedAt']);

  // Insert into registrations table
  $sql = "INSERT INTO registrations (first_name, last_name, email, cell_phone, banquet_attendees, total_cost, submitted_at) 
            VALUES ('$firstName', '$lastName', '$email', '$cellPhone', $banquetAttendees, $totalCost, '$submittedAt')";

  if (!$conn->query($sql)) {
    throw new Exception("Error inserting registration: " . $conn->error);
  }

  // Get the inserted registration ID
  $registrationId = $conn->insert_id;

  // Insert additional attendees if any
  if (isset($data['additionalAttendees']) && is_array($data['additionalAttendees'])) {
    foreach ($data['additionalAttendees'] as $attendee) {
      $addFirstName = $conn->real_escape_string($attendee['firstName']);
      $addLastName = $conn->real_escape_string($attendee['lastName']);

      $sql = "INSERT INTO registrations_others (registration_id, first_name, last_name) 
                    VALUES ($registrationId, '$addFirstName', '$addLastName')";

      if (!$conn->query($sql)) {
        throw new Exception("Error inserting additional attendee: " . $conn->error);
      }
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
    $mail->addAddress('eaglegolf90@gmail.com');
    $mail->addReplyTo($email, "$firstName $lastName");

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Registration: Christmas Luncheon - $firstName $lastName";

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
                <h1>New Christmas Luncheon Registration</h1>
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

  } catch (Exception $e) {
    // Log email error but don't fail the registration
    error_log("Email sending failed: " . $mail->ErrorInfo);
  }

  // Return success response
  echo json_encode([
    'success' => true,
    'message' => 'Registration successful',
    'registrationId' => $registrationId
  ]);

} catch (Exception $e) {
  // Return error response
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>