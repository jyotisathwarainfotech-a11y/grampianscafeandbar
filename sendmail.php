<?php
/**
 * Contact Form – Dual Email Sender
 * Sends:
 * 1) Full enquiry email to Manager
 * 2) Confirmation email to User
 */

header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

function sendResponse($success, $message, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => (bool)$success,
        'message' => (string)$message
    ]);
    exit;
}

/* -------------------------
   Request validation
--------------------------*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.', 405);
}

/* -------------------------
   Sanitize inputs
--------------------------*/
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
$email   = filter_var($email, FILTER_SANITIZE_EMAIL);

/* -------------------------
   Validate inputs
--------------------------*/
if ($name === '') sendResponse(false, 'Please enter your name.', 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) sendResponse(false, 'Please enter a valid email.', 400);
if ($subject === '') sendResponse(false, 'Please enter subject.', 400);
if ($message === '') sendResponse(false, 'Please enter message.', 400);

/* -------------------------
   Email addresses
--------------------------*/
$managerEmail = 'manger.grampianscafe1@gmail.com';
$siteFrom     = 'no-reply@grampianscafeandbar.com'; // MUST be your domain

/* -------------------------
   1️⃣ EMAIL TO MANAGER
--------------------------*/
$managerSubject = "New Contact Form Enquiry – $subject";

$managerBody  = "New Contact Form Submission\n";
$managerBody .= "============================\n\n";
$managerBody .= "Name    : $name\n";
$managerBody .= "Email   : $email\n";
$managerBody .= "Subject : $subject\n";
$managerBody .= "Date    : " . date('d-m-Y H:i:s') . "\n\n";
$managerBody .= "Message:\n";
$managerBody .= "---------------------------------\n";
$managerBody .= $message . "\n";
$managerBody .= "---------------------------------\n";

$managerHeaders  = "MIME-Version: 1.0\r\n";
$managerHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
$managerHeaders .= "From: Grampians Cafe & Bar <$siteFrom>\r\n";
$managerHeaders .= "Reply-To: $name <$email>\r\n";

/* -------------------------
   2️⃣ EMAIL TO USER
--------------------------*/
$userSubject = "Thank you for contacting Grampians Cafe & Bar";

$userBody  = "Hi $name,\n\n";
$userBody .= "Thank you for contacting Grampians Cafe & Bar.\n";
$userBody .= "We have received your message and our team will get back to you shortly.\n\n";
$userBody .= "Your Message:\n";
$userBody .= "---------------------------------\n";
$userBody .= $message . "\n";
$userBody .= "---------------------------------\n\n";
$userBody .= "Regards,\n";
$userBody .= "Grampians Cafe & Bar Team\n";

$userHeaders  = "MIME-Version: 1.0\r\n";
$userHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
$userHeaders .= "From: Grampians Cafe & Bar <$siteFrom>\r\n";

/* -------------------------
   Send emails
--------------------------*/
try {
    $sendToManager = mail($managerEmail, $managerSubject, $managerBody, $managerHeaders);
    $sendToUser    = mail($email, $userSubject, $userBody, $userHeaders);

    if ($sendToManager && $sendToUser) {
        sendResponse(true, 'Thank you! Your message has been sent successfully.');
    } else {
        error_log('Mail failed. Manager: ' . ($sendToManager ? 'OK' : 'FAIL') . ', User: ' . ($sendToUser ? 'OK' : 'FAIL'));
        sendResponse(false, 'Unable to send email. Please try again later.', 500);
    }
} catch (Exception $e) {
    error_log('Mail Exception: ' . $e->getMessage());
    sendResponse(false, 'Server error. Please try again later.', 500);
}
