<?php
// Enable error reporting (helps debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fixed recipient
define("RECIPIENT_NAME", "Mehar Imran");
define("RECIPIENT_EMAIL", "Cityenterprises533@gmail.com");

// Helper: prevent header injection
function sanitize_header($value) {
    // remove CRLF and suspicious characters
    return preg_replace('/[\r\n\t\0\x0B]+/', ' ', trim($value));
}

// Read form values safely
$userName = isset($_POST['username']) ? sanitize_header($_POST['username']) : "";
$senderEmail = isset($_POST['email']) ? trim($_POST['email']) : "";
$senderPhone = isset($_POST['phone']) ? sanitize_header($_POST['phone']) : "";
$userSubject = isset($_POST['subject']) ? sanitize_header($_POST['subject']) : "";
$message = isset($_POST['message']) ? trim($_POST['message']) : "";

// Basic validation
if (!$userName || !$senderEmail || !$senderPhone || !$userSubject || !$message) {
    header('Location: contact.html?message=MissingFields');
    exit;
}

// validate email
if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.html?message=InvalidEmail');
    exit;
}

$recipient = RECIPIENT_EMAIL;
$subject = "Contact Form: " . $userSubject;

// Message body
$msgBody = "You have received a new message from your website contact form.\n\n";
$msgBody .= "----------------------------------\n";
$msgBody .= "Name: $userName\n";
$msgBody .= "Email: $senderEmail\n";
$msgBody .= "Phone: $senderPhone\n";
$msgBody .= "Subject: $userSubject\n";
$msgBody .= "----------------------------------\n\n";
$msgBody .= "Message:\n" . $message . "\n";

// Prepare headers (use CRLF). Use a domain-based From address to reduce spam filtering.
$serverDomain = preg_replace('/[^A-Za-z0-9\.\-]/', '', $_SERVER['SERVER_NAME']);
$fromAddress = 'no-reply@' . ($serverDomain ?: 'localhost');

$headers = "From: " . RECIPIENT_NAME . " <" . $fromAddress . ">\r\n";
$headers .= "Reply-To: " . $senderEmail . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Use additional parameters to set the envelope sender (helps deliverability on many systems)
$additional_parameters = "-f" . $fromAddress;

// Try to send the email
$success = false;
try {
    $success = mail($recipient, $subject, $msgBody, $headers, $additional_parameters);
} catch (Exception $e) {
    // log exception to server error log
    error_log('Mail error: ' . $e->getMessage());
    $success = false;
}

if ($success) {
    header('Location: contact.html?message=Success');
    exit;
} else {
    // log failure for debugging
    error_log('Mail failed to send to ' . $recipient . ' from ' . $senderEmail);
    header('Location: contact.html?message=Failed');
    exit;
}

?>
