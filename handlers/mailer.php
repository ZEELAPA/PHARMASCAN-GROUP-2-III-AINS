<?php
// Since you have a 'vendor' folder, we use the autoloader:
require '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($toEmail, $toName, $subject, $bodyHTML) {
    $mail = new PHPMailer(true);

    try {
        // --- Server Settings ---
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'PharmaScanSystem@gmail.com';
        // Use your App Password here (no spaces if you get auth errors):
        $mail->Password   = 'bxnrzaniqdqwpedv'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Recipients ---
        $mail->setFrom('PharmaScanSystem@gmail.com', 'PharmaScan Admin');
        $mail->addAddress($toEmail, $toName);

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHTML;
        $mail->AltBody = strip_tags($bodyHTML);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>