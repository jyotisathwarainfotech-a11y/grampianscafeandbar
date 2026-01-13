<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* =========================================
   COMPOSER AUTOLOAD (REQUIRED)
========================================= */
require __DIR__ . '/vendor/autoload.php';

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

    /* ---- Mail to Manager ---- */
    $mail->addAddress($env['RECIPIENT_EMAIL']);
    $mail->addReplyTo($data['email'], $data['name']);

    $mail->Subject = "New Contact Enquiry – {$data['subject']}";
    $mail->Body =
        "Contact Form Submission\n\n" .
        "Name: {$data['name']}\n" .
        "Email: {$data['email']}\n" .
        "Subject: {$data['subject']}\n\n" .
        "Message:\n{$data['message']}";

    $mail->send();

    /* ---- Confirmation to User ---- */
    $mail->clearAddresses();
    $mail->clearReplyTos();

    $mail->addAddress($data['email'], $data['name']);
    $mail->Subject = "Thank you for contacting Grampians Cafe & Bar";
    $mail->Body =
        "Hi {$data['name']},\n\n" .
        "Thank you for contacting Grampians Cafe & Bar.\n" .
        "We have received your message and will respond shortly.\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    $mail->send();
}

/* =========================================
   SEND RESERVATION MAIL
========================================= */
function sendReservationMail(array $env, array $data): void
{
    $mail = createMailer($env);

    /* ---- Mail to Manager ---- */
    $mail->addAddress($env['RECIPIENT_EMAIL']);
    $mail->addReplyTo($data['email'], $data['name']);

    $mail->Subject = "New Table Reservation Request";
    $mail->Body =
        "Reservation Request\n\n" .
        "Name: {$data['name']}\n" .
        "Email: {$data['email']}\n" .
        "Phone: {$data['phone']}\n" .
        "Guests: {$data['guests']}\n" .
        "Date: {$data['date']}\n" .
        "Preferred Time: {$data['time']}\n\n" .
        "Special Request:\n{$data['request']}";

    $mail->send();

    /* ---- Confirmation to User ---- */
    $mail->clearAddresses();
    $mail->clearReplyTos();

    $mail->addAddress($data['email'], $data['name']);
    $mail->Subject = "Reservation Request Received – Grampians Cafe & Bar";
    $mail->Body =
        "Hi {$data['name']},\n\n" .
        "Thank you for your reservation request.\n\n" .
        "Reservation Details:\n" .
        "Guests: {$data['guests']}\n" .
        "Date: {$data['date']}\n" .
        "Time: {$data['time']}\n\n" .
        "We will confirm your booking shortly.\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    $mail->send();
}

/* =========================================
   REQUEST HANDLER
========================================= */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', 405);
    }

    $type = $_POST['type'] ?? '';

    /* -------- CONTACT FORM -------- */
    if ($type === 'contact') {
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'subject' => trim($_POST['subject'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];

        if (!$data['name'] || !$data['email'] || !$data['subject'] || !$data['message']) {
            sendResponse(false, 'All contact fields are required', 400);
        }

        sendContactMail($env, $data);
        sendResponse(true, 'Contact message sent successfully');
    }

    /* -------- RESERVATION FORM -------- */
    if ($type === 'reservation') {
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'phone'   => trim($_POST['phone'] ?? ''),
            'guests'  => trim($_POST['guests'] ?? ''),
            'date'    => trim($_POST['date'] ?? ''),
            'time'    => trim($_POST['time'] ?? ''),
            'request' => trim($_POST['request'] ?? 'None')
        ];

        if (
            !$data['name'] ||
            !$data['email'] ||
            !$data['phone'] ||
            !$data['guests'] ||
            !$data['date'] ||
            !$data['time']
        ) {
            sendResponse(false, 'All reservation fields are required', 400);
        }

        sendReservationMail($env, $data);
        sendResponse(true, 'Reservation request sent successfully');
    }

    sendResponse(false, 'Invalid form type', 400);

} catch (Exception $e) {
    error_log('MAIL ERROR: ' . $e->getMessage());
    sendResponse(false, 'Server error. Please try again later.', 500);
}
