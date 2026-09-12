<?php
// includes/mailer.php
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_verification_email(string $toEmail, string $toName, string $token): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $verifyLink = SITE_URL . '/verify.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Confirme ton compte Softener Lab';
        $mail->Body    = "Bonjour {$toName},<br><br>
            Merci de ton inscription. Confirme ton adresse email en cliquant sur ce lien :<br>
            <a href=\"{$verifyLink}\">{$verifyLink}</a><br><br>
            Si tu n'es pas à l'origine de cette inscription, ignore ce message.";
        $mail->AltBody = "Confirme ton compte en ouvrant ce lien : {$verifyLink}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Erreur envoi email: ' . $mail->ErrorInfo);
        return false;
    }
}
