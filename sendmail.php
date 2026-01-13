<?php
// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if PHPMailer is installed
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    error_log("PHPMailer not installed. Run 'composer install' in the project directory.");
    die("Configuration error. Please contact the administrator.");
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST["name"] ?? ''));
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST["subject"] ?? ''));
    $message = htmlspecialchars(trim($_POST["message"] ?? ''));

    // Basic validation
    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo "Please fill in all fields.";
        exit;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email address.";
        exit;
    }

    try {
        // Load environment variables from .env file
        $envFile = __DIR__ . '/.env';
        if(!file_exists($envFile)) {
            http_response_code(500);
            error_log(".env file not found at: $envFile");
            die("Configuration error. Please contact the administrator.");
        }

        $env = parse_ini_file($envFile);
        if($env === false) {
            http_response_code(500);
            error_log("Failed to parse .env file");
            die("Configuration error. Please contact the administrator.");
        }

        // Validate required environment variables
        $required = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_FROM_EMAIL', 'RECIPIENT_EMAIL'];
        foreach($required as $var) {
            if(empty($env[$var])) {
                http_response_code(500);
                error_log("Missing environment variable: $var");
                die("Configuration error. Please contact the administrator.");
            }
        }

        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USERNAME'];
        $mail->Password = $env['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)$env['SMTP_PORT'];
        $mail->Timeout = 10;

        // Recipients
        $mail->setFrom($env['SMTP_FROM_EMAIL'], $env['SMTP_FROM_NAME'] ?? 'Grampians Cafe & Bar');
        $mail->addAddress($env['RECIPIENT_EMAIL']);
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        
        // Create email body
        $emailBody = "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Subject: $subject\n";
        $emailBody .= "-------------------\n\n";
        $emailBody .= "Message:\n";
        $emailBody .= $message;
        
        $mail->Body = $emailBody;

        if($mail->send()) {
            http_response_code(200);
            echo "success";
        } else {
            http_response_code(500);
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            echo "Error sending message. Please try again later.";
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Exception in sendmail.php: " . $e->getMessage());
        echo "Message could not be sent. Please try again later.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
?>
