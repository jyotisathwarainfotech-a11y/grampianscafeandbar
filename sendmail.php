<?php
declare(strict_types=1);

/* =========================================
   MANUAL LOAD FROM COMPOSER INSTALL
========================================= */
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* =========================================
   RESPONSE / ERROR CONFIG
========================================= */
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');

/* =========================================
   LOAD ENV FILE
========================================= */
$env = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW);

/* =========================================
   JSON RESPONSE HELPER
========================================= */
function sendResponse(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

/* =========================================
   CREATE SMTP MAILER
========================================= */
function createMailer(array $env): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $env['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USERNAME'];
    $mail->Password   = $env['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)($env['SMTP_PORT'] ?? 587);
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(
        $env['SMTP_FROM_EMAIL'],
        $env['SMTP_FROM_NAME']
    );

    return $mail;
}

/* =========================================
   SEND CONTACT FORM MAIL
========================================= */
function sendContactMail(array $env, array $data): void
{
    $mail = createMailer($env);

    $mail->addAddress($env['RECIPIENT_EMAIL']);
    $mail->addReplyTo($data['email'], $data['name']);

    $mail->Subject = "New Contact Enquiry – {$data['subject']}";
    $mail->Body =
        "Name: {$data['name']}\n" .
        "Email: {$data['email']}\n" .
        "Subject: {$data['subject']}\n\n" .
        "Message:\n{$data['message']}";

    $mail->send();

    $mail->clearAddresses();
    $mail->clearReplyTos();

    $mail->addAddress($data['email'], $data['name']);
    $mail->Subject = "Thank you for contacting Grampians Cafe & Bar";
    $mail->Body =
        "Hi {$data['name']},\n\n" .
        "We have received your message and will respond shortly.\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    $mail->send();
}

/* =========================================
   REQUEST HANDLER (CONTACT ONLY FOR TEST)
========================================= */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', 405);
    }

    $data = [
        'name'    => trim($_POST['name'] ?? ''),
        'email'   => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
        'subject' => trim($_POST['subject'] ?? ''),
        'message' => trim($_POST['message'] ?? '')
    ];

    if (!$data['name'] || !$data['email'] || !$data['subject'] || !$data['message']) {
        sendResponse(false, 'All fields are required', 400);
    }

    sendContactMail($env, $data);
    sendResponse(true, 'Contact message sent successfully');

} catch (Exception $e) {
    error_log('MAIL ERROR: ' . $e->getMessage());
    sendResponse(false, 'Server error. Please try again later.', 500);
}
