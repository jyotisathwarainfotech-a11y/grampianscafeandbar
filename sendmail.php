<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Basic validation
    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "Please fill in all fields.";
        exit;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit;
    }

    // Load environment variables (if using .env file)
    $envFile = __DIR__ . '/.env';
    if(file_exists($envFile)) {
        $env = parse_ini_file($envFile);
    } else {
        // Use default Gmail configuration
        $env = array(
            'SMTP_HOST' => 'smtp.gmail.com',
            'SMTP_PORT' => 587,
            'SMTP_USERNAME' => getenv('SMTP_USERNAME') ?: 'your-email@gmail.com',
            'SMTP_PASSWORD' => getenv('SMTP_PASSWORD') ?: 'your-app-password',
            'SMTP_FROM_EMAIL' => getenv('SMTP_FROM_EMAIL') ?: 'manger.grampianscafe1@gmail.com',
            'SMTP_FROM_NAME' => getenv('SMTP_FROM_NAME') ?: 'Grampians Cafe & Bar',
            'RECIPIENT_EMAIL' => getenv('RECIPIENT_EMAIL') ?: 'tanvimalaviya2004@gmail.com'
        );
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USERNAME'];
        $mail->Password = $env['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $env['SMTP_PORT'];

        // Recipients
        $mail->setFrom($env['SMTP_FROM_EMAIL'], $env['SMTP_FROM_NAME']);
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
            echo "success";
        } else {
            echo "Error: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
?>
