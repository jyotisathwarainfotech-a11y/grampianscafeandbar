<?php
// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars(trim($_POST["name"] ?? ''));
$email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($_POST["subject"] ?? ''));
$message = htmlspecialchars(trim($_POST["message"] ?? ''));

// Basic validation
if(empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

try {
    // Try using PHPMailer if available
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        // Load environment variables from .env file
        $envFile = __DIR__ . '/.env';
        if(!file_exists($envFile)) {
            throw new Exception(".env file not found");
        }

        $env = parse_ini_file($envFile);
        if($env === false) {
            throw new Exception("Failed to parse .env file");
        }

        // Validate required environment variables
        $required = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_FROM_EMAIL', 'RECIPIENT_EMAIL'];
        foreach($required as $var) {
            if(empty($env[$var])) {
                throw new Exception("Missing environment variable: $var");
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
            echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
        } else {
            throw new Exception("PHPMailer Error: " . $mail->ErrorInfo);
        }
    } else {
        // Fallback: Use PHP mail() function
        $to = "tanvimalaviya2004@gmail.com";
        $headers = "From: $name <$email>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        $body = "Name: $name\n";
        $body .= "Email: $email\n";
        $body .= "Subject: $subject\n";
        $body .= "-------------------\n\n";
        $body .= "Message:\n";
        $body .= $message;

        if(mail($to, $subject, $body, $headers)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
        } else {
            throw new Exception("Failed to send email using mail() function");
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in sendmail.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error sending message. Please try again later.']);
}
?>
