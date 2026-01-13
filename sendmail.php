<?php
header('Content-Type: application/json; charset=UTF-8');

/* =====================================
   RESPONSE HELPER
===================================== */
function sendResponse($ok, $msg, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => (bool)$ok,
        'message' => $msg
    ]);
    exit;
}

/* =====================================
   SEND MAIL WRAPPER
===================================== */
function sendMail($to, $subject, $body, $replyTo = null)
{
    $headers  = "From: Grampians Cafe & Bar <no-reply@grampianscafeandbar.com.au>\r\n";
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($to, $subject, $body, $headers);
}

/* =====================================
   VALIDATE REQUEST
===================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', 405);
}

/* =====================================
   ROUTER
===================================== */
$type = $_POST['type'] ?? '';

$managerEmail = 'manger.grampianscafe1@gmail.com';

/* =====================================
   CONTACT FORM
===================================== */
if ($type === 'contact') {

    $name    = trim($_POST['name'] ?? '');
    $email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        sendResponse(false, 'All contact fields are required', 400);
    }

    /* --- Mail to Manager --- */
    $adminBody =
        "New Contact Enquiry\n\n" .
        "Name: $name\n" .
        "Email: $email\n" .
        "Subject: $subject\n\n" .
        "Message:\n$message";

    sendMail(
        $managerEmail,
        "New Contact Enquiry – $subject",
        $adminBody,
        $email
    );

    /* --- Confirmation to User --- */
    $userBody =
        "Hi $name,\n\n" .
        "Thank you for contacting Grampians Cafe & Bar.\n" .
        "We have received your message and will respond shortly.\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    sendMail(
        $email,
        "Thank you for contacting Grampians Cafe & Bar",
        $userBody
    );

    sendResponse(true, 'Contact message sent successfully');
}

/* =====================================
   RESERVATION FORM
===================================== */
if ($type === 'reservation') {

    $name    = trim($_POST['name'] ?? '');
    $email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone   = trim($_POST['phone'] ?? '');
    $guests  = trim($_POST['guests'] ?? '');
    $date    = trim($_POST['date'] ?? '');
    $time    = trim($_POST['time'] ?? '');
    $request = trim($_POST['request'] ?? 'None');

    if (!$name || !$email || !$phone || !$guests || !$date || !$time) {
        sendResponse(false, 'All reservation fields are required', 400);
    }

    /* --- Mail to Manager --- */
    $adminBody =
        "New Reservation Request\n\n" .
        "Name: $name\n" .
        "Email: $email\n" .
        "Phone: $phone\n" .
        "Guests: $guests\n" .
        "Date: $date\n" .
        "Preferred Time: $time\n\n" .
        "Special Request:\n$request";

    sendMail(
        $managerEmail,
        "New Table Reservation Request",
        $adminBody,
        $email
    );

    /* --- Confirmation to User --- */
    $userBody =
        "Hi $name,\n\n" .
        "Thank you for your reservation request at Grampians Cafe & Bar.\n\n" .
        "Reservation Details:\n" .
        "Guests: $guests\n" .
        "Date: $date\n" .
        "Time: $time\n\n" .
        "We will confirm your booking shortly.\n\n" .
        "Regards,\nGrampians Cafe & Bar Team";

    sendMail(
        $email,
        "Reservation Request Received – Grampians Cafe & Bar",
        $userBody
    );

    sendResponse(true, 'Reservation request sent successfully');
}

/* =====================================
   INVALID TYPE
===================================== */
sendResponse(false, 'Invalid form type', 400);
