<?php
/**
 * config/mail.php
 * Mail Configuration using PHPMailer & Gmail SMTP
 */

// These credentials should ideally be in a .env file, but we use this config file for this project structure.
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // STARTTLS
define('MAIL_USERNAME', 'syitrollno63@gmail.com');
define('MAIL_PASSWORD', 'wfjpjlbwrmvnompt');
define('MAIL_FROM_ADDRESS', 'syitrollno63@gmail.com');
define('MAIL_FROM_NAME', 'TuneHub Admin');

// A helper function to get an active PHPMailer instance
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Manually require PHPMailer core files (Composer bypassed for academic simplicity)
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

function get_mailer()
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;

        // Sender info default
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        return $mail;
    } catch (Exception $e) {
        // Just return null or throw exception based on project preference
        throw new Exception("PHPMailer Config Error: " . $mail->ErrorInfo);
    }
}
?>