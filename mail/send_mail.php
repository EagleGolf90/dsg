<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Read and decode JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Sanitize inputs
$firstName      = htmlspecialchars(trim($input['firstName'] ?? ''));
$lastName       = htmlspecialchars(trim($input['lastName'] ?? ''));
$email          = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$cellPhone      = htmlspecialchars(trim($input['cellPhone'] ?? ''));
$totalAttendees = intval($input['totalAttendees'] ?? 1);
$totalCost      = number_format(floatval($input['totalCost'] ?? 0), 2);
$additionalAttendees = $input['additionalAttendees'] ?? [];
$submittedAt    = htmlspecialchars(trim($input['submittedAt'] ?? ''));

if (empty($firstName) || empty($lastName) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Build additional attendees HTML
$additionalHtml = '';
$additionalText = '';
if (!empty($additionalAttendees)) {
    $additionalHtml .= '<ul>';
    foreach ($additionalAttendees as $index => $attendee) {
        $aFirst = htmlspecialchars(trim($attendee['firstName'] ?? ''));
        $aLast  = htmlspecialchars(trim($attendee['lastName'] ?? ''));
        $num    = $index + 2;
        $additionalHtml .= "<li>Attendee {$num}: {$aFirst} {$aLast}</li>";
        $additionalText .= "Attendee {$num}: {$aFirst} {$aLast}\n";
    }
    $additionalHtml .= '</ul>';
} else {
    $additionalHtml = '<p>None</p>';
    $additionalText = 'None';
}

$htmlBody = "
<html><body style='font-family: Arial, sans-serif; color: #333;'>
<h2>Christmas Luncheon Registration</h2>
<p><strong>Deaf Seniors of Georgia &mdash; December 5, 2026</strong></p>
<hr>
<h3>Primary Registrant</h3>
<p><strong>Name:</strong> {$firstName} {$lastName}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Cell Phone:</strong> {$cellPhone}</p>
<h3>Attendance</h3>
<p><strong>Total Attendees:</strong> {$totalAttendees}</p>
<p><strong>Total Cost:</strong> \${$totalCost}</p>
<h3>Additional Attendees</h3>
{$additionalHtml}
<hr>
<p style='color:#888; font-size:12px;'>Submitted: {$submittedAt}</p>
</body></html>
";

$textBody = "Christmas Luncheon Registration\n"
    . "Deaf Seniors of Georgia - December 5, 2026\n\n"
    . "PRIMARY REGISTRANT\n"
    . "Name: {$firstName} {$lastName}\n"
    . "Email: {$email}\n"
    . "Cell Phone: {$cellPhone}\n\n"
    . "ATTENDANCE\n"
    . "Total Attendees: {$totalAttendees}\n"
    . "Total Cost: \${$totalCost}\n\n"
    . "ADDITIONAL ATTENDEES\n"
    . $additionalText . "\n"
    . "Submitted: {$submittedAt}\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'mail.fulltimberstack.dev';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'admin@fulltimberstack.dev';
    $mail->Password   = '!mNmhbPk9hKBKAEB';
    $mail->Port       = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom('admin@fulltimberstack.dev', 'DSG Registration');
    $mail->addAddress('eaglegolf90@gmail.com', 'DSG Admin');
    // Send confirmation copy to registrant
    // $mail->addCC($email, "{$firstName} {$lastName}");

    $mail->isHTML(true);
    $mail->Subject = "DSG Luncheon Registration - {$firstName} {$lastName}";
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Registration submitted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Email could not be sent: ' . $mail->ErrorInfo]);
}
?>