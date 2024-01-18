<?php
/**
 * Class mailer that allows to simply create and send emails
 * given the type of email and the data to be sent. Uses the 
 * PHPMailer library that has been imported.
 * 
 * The recipient email is specified in the constructor.
 * 
 * Mail templates are stored in the mail_templates folder.
 */
require 'keys.php';
require 'php_mailer/PHPMailer.php';
require 'php_mailer/SMTP.php';
require 'php_mailer/Exception.php'; 

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer{
    private $mail; // PHPMailer object

    // Specify the user's email address
    public function __construct($recipientEmail) {
        $this->mail = new PHPMailer(true);

        // Configure PHPMailer with SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USERNAME;
        $this->mail->Password = SMTP_PASSWORD;
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = SMTP_PORT;

        // Common configurations
        $this->mail->setFrom('no-reply@onacid.net', 'Stickit\' Truddy');
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
    }

    public function sendConfirmCode($code) {
        try {
            // Load the template
            $template = file_get_contents(EmailTemplates::CONFIRMATION_TEMPLATE);
            $template = str_replace('{{code}}', $code, $template);

            // Set email content
            $this->mail->Subject = 'Your Confirmation Code';
            $this->mail->Body = $template;
            $this->mail->send();
            echo 'Confirmation email has been sent';
        } catch (Exception $e) {
            echo "Confirmation email could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}

/**
 * Class EmailTemplates that contains the paths to the email templates
 */
class EmailTemplates {
    const CONFIRMATION_TEMPLATE = 'mail_templates/confirm_code.html';
}

// Test
$mailer = new Mailer(TEST_EMAIL);
$mailer->sendConfirmCode("TRUDDY SUPREMACY!");

?>

