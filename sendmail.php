<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===============================
   COMPOSER AUTOLOAD
================================ */
require __DIR__ . '/vendor/autoload.php';

/* ===============================
   RESPONSE CONFIG
================================ */
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

/* ===============================
   LOAD .env FILE
================================ */
$env = parse_ini_file(__DIR__ . '/.env');

function sendResponse($success, $message, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => (bool)$success,
        'message' => $message
    ]);
    exit;
}

/* ===============================
   VALIDATE REQUEST
================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', 405);
}

/* ===============================
   SANITIZE INPUT
================================ */
$name    = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES);
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES);
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES);

if (!$name || !$email || !$subject || !$message) {
    sendResponse(false, 'All fields are required', 400);
}

/* ===============================
   SMTP CONFIG FROM .env
================================ */
$smtpHost = $env['SMTP_HOST'] ?? '';
$smtpPort = $env['SMTP_PORT'] ?? 587;
$smtpUser = $env['SMTP_USERNAME'] ?? '';
$smtpPass = $env['SMTP_PASSWORD'] ?? '';
$fromMail = $env['SMTP_FROM_EMAIL'] ?? '';
$fromName = $env['SMTP_FROM_NAME'] ?? 'Website';
$manager  = $env['RECIPIENT_EMAIL'] ?? '';

if (!$smtpHost || !$smtpUser || !$smtpPass || !$fromMail || !$manager) {
    sendResponse(false, 'Mail server configuration error.', 500);
}

try {
    /* =====================================================
       1️⃣ MAIL TO MANAGER
    ======================================================*/
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtpPort;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($fromMail, $fromName);
    $mail->addAddress($manager);
    $mail->addReplyTo($email, $name);

    $mail->Subject = "New Contact Form Enquiry – $subject";
    $mail->Body =
        "New Contact Form Submission\n\n" .
        "Name: $name\n" .
        "Email: $email\n" .
        "Subject: $subject\n\n" .
        "Message:\n$message";

    $mail->send();

    /* =====================================================
       2️⃣ MAIL TO USER (CONFIRMATION)
    ======================================================*/
    $mail->clearAddresses();
    $mail->clearReplyTos();

    $mail->addAddress($email, $name);
    $mail->Subject = "Thank you for contacting Grampians Cafe & Bar";
    $mail->Body =
        "Hi $name,\n\n" .
        "Thank you for contacting Grampians Cafe & Bar.\n" .
        "We have received your message and will contact you shortly.\n\n" .
        "Your Message:\n$message\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    $mail->send();

    sendResponse(true, 'Thank you! Your message has been sent successfully.');

} catch (Exception $e) {
    error_log('SMTP Error: ' . $mail->ErrorInfo);
    sendResponse(false, 'Email sending failed. Please try again later.', 500);
}
