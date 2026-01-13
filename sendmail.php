<?php
/**
 * Contact Form Email Handler
 * Handles form submissions and sends emails via PHP mail() function
 */

// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Suppress default error output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Helper function to send JSON response
function sendResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => (bool)$success,
        'message' => (string)$message
    ]);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.', 405);
}

// Trim and sanitize inputs
$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8') : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8') : '';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8') : '';

// Validate required fields
if (empty($name)) {
    sendResponse(false, 'Please enter your name.', 400);
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Please enter a valid email address.', 400);
}

if (empty($subject)) {
    sendResponse(false, 'Please enter a subject.', 400);
}

if (empty($message)) {
    sendResponse(false, 'Please enter your message.', 400);
}

// Additional validation - prevent spam
if (strlen($name) > 100) {
    sendResponse(false, 'Name is too long.', 400);
}

if (strlen($subject) > 200) {
    sendResponse(false, 'Subject is too long.', 400);
}

if (strlen($message) > 5000) {
    sendResponse(false, 'Message is too long.', 400);
}

// Define recipient email
$recipient = 'tanvimalaviya2004@gmail.com';

// Validate recipient email
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid recipient email configured");
    sendResponse(false, 'Server configuration error.', 500);
}

// Construct email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: " . $name . " <" . $email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Construct email body
$body = "Contact Form Submission\n";
$body .= "========================\n\n";
$body .= "Name: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Subject: " . $subject . "\n";
$body .= "Date: " . date('Y-m-d H:i:s') . "\n";
$body .= "________________________\n\n";
$body .= "Message:\n";
$body .= $message . "\n";
$body .= "\n________________________\n";
$body .= "This is an automated response. Please do not reply to this email.\n";

// Attempt to send email
try {
    $mail_result = @mail(
        $recipient,
        "New Contact Form Submission: " . $subject,
        $body,
        $headers,
        '-f' . $email
    );

    if ($mail_result === true) {
        sendResponse(true, 'Thank you! Your message has been sent successfully.', 200);
    } else {
        error_log("mail() function returned false. Recipient: $recipient, Subject: $subject");
        sendResponse(false, 'Unable to send email. Please try again later.', 500);
    }
} catch (Exception $e) {
    error_log("Exception in sendmail.php: " . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred. Please try again later.', 500);
}
