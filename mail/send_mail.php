<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
  // Server settings
  $mail->isSMTP();
  $mail->Host = 'mail.fulltimberstack.dev';
  $mail->SMTPAuth = true;
  $mail->Username = 'admin@fulltimberstack.dev';
  $mail->Password = '!mNmhbPk9hKBKAEB';
  $mail->Port = 25;   // or 8889 if 25 is blocked
  $mail->SMTPSecure = false;
  $mail->SMTPAutoTLS = false;

  // Sender & recipient
  $mail->setFrom('admin@fulltimberstack.dev', 'FullTimberStack Mailer');
  $mail->addAddress('btimberlake@twc.com');

  // Content
  $mail->isHTML(true);
  $mail->Subject = 'Test Email from fulltimberstack.dev';
  $mail->Body = '<p>Hello! This is a test email sent using <strong>PHPMailer</strong> on SmarterASP.NET.</p>';
  $mail->AltBody = 'Hello! This is a test email sent using PHPMailer on SmarterASP.NET.';

  $mail->send();
  echo 'Email sent successfully!';
} catch (Exception $e) {
  echo "Email failed: {$mail->ErrorInfo}";
}
?>