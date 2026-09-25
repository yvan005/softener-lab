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

function send_password_reset_email(string $toEmail, string $toName, string $token): bool
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

        $resetLink = SITE_URL . '/reset-password.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Réinitialise ton mot de passe Softener Lab';
        $mail->Body    = "Bonjour {$toName},<br><br>
            Tu as demandé à réinitialiser ton mot de passe. Clique sur ce lien pour en choisir un nouveau (valable 1 heure) :<br>
            <a href=\"{$resetLink}\">{$resetLink}</a><br><br>
            Si tu n'es pas à l'origine de cette demande, ignore ce message : ton mot de passe actuel reste inchangé.";
        $mail->AltBody = "Réinitialise ton mot de passe en ouvrant ce lien (valable 1 heure) : {$resetLink}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Erreur envoi email réinitialisation: ' . $mail->ErrorInfo);
        return false;
    }
}

function send_contact_email(string $name, string $email, string $company, string $service, string $message): bool
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

        // Le message arrive dans la boîte de Softener Lab, avec le
        // visiteur en Reply-To pour pouvoir lui répondre directement.
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addReplyTo($email, $name);

        $safeName    = htmlspecialchars($name);
        $safeCompany = htmlspecialchars($company !== '' ? $company : '—');
        $safeService = htmlspecialchars($service !== '' ? $service : '—');
        $safeMessage = nl2br(htmlspecialchars($message));

        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message de contact — ' . ($service !== '' ? $service : 'Site web');
        $mail->Body    = "Nouveau message depuis le formulaire de contact du site :<br><br>
            <strong>Nom :</strong> {$safeName}<br>
            <strong>Entreprise :</strong> {$safeCompany}<br>
            <strong>Email :</strong> " . htmlspecialchars($email) . "<br>
            <strong>Service concerné :</strong> {$safeService}<br><br>
            <strong>Message :</strong><br>{$safeMessage}";
        $mail->AltBody = "Nom: {$name}\nEntreprise: {$company}\nEmail: {$email}\nService: {$service}\n\nMessage:\n{$message}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Erreur envoi email contact: ' . $mail->ErrorInfo);
        return false;
    }
}

/* ============================================================
   Notifications (commandes, demandes de formation)
   ============================================================ */

/** Client SMTP prêt à l'emploi. Timeout court pour ne jamais bloquer une page. */
function new_smtp_mailer(): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 10;
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    return $mail;
}

function mail_h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

/** Gabarit HTML commun des emails. $button = [libellé, url] (facultatif). */
function email_layout(string $heading, string $bodyHtml, ?array $button = null, string $footer = ''): string
{
    $btn = '';
    if ($button) {
        $btn = '<p style="margin:24px 0;"><a href="' . mail_h($button[1]) . '" style="background:#3DDC84;color:#08090B;'
             . 'padding:12px 22px;border-radius:4px;text-decoration:none;font-weight:700;display:inline-block;">'
             . mail_h($button[0]) . '</a></p>';
    }
    return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#0E1210;line-height:1.55;">'
         . '<div style="background:#08090B;color:#3DDC84;padding:18px 24px;font-weight:700;letter-spacing:.02em;">Softener Lab</div>'
         . '<div style="padding:24px;border:1px solid #DCDFDA;border-top:0;">'
         . '<h2 style="margin:0 0 16px;font-size:20px;">' . mail_h($heading) . '</h2>'
         . $bodyHtml . $btn
         . '<p style="color:#5B655E;font-size:13px;margin:28px 0 0;">' . $footer . '</p>'
         . '</div></div>';
}

function send_html_email(string $toEmail, string $toName, string $subject, string $html, string $alt, ?array $replyTo = null): bool
{
    try {
        $mail = new_smtp_mailer();
        $mail->addAddress($toEmail, $toName);
        if ($replyTo) $mail->addReplyTo($replyTo[0], $replyTo[1] ?? '');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $alt;
        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('Erreur envoi email (' . $subject . '): ' . $e->getMessage());
        return false;
    }
}

function email_quote(string $note): string
{
    if (trim($note) === '') return '';
    return '<div style="margin:18px 0;padding:12px 16px;border-left:4px solid #3DDC84;background:#F4F5F3;">'
         . '<strong>Message de Softener Lab :</strong><br>' . nl2br(mail_h($note)) . '</div>';
}

/** Email au membre quand le statut d'une commande change (ou qu'un message lui est adressé). */
function send_order_status_email(string $toEmail, string $toName, int $orderId, string $ref, string $title, string $service, string $status, string $note = ''): bool
{
    $texts = [
        'pending'     => ['Ta commande est de nouveau en attente', "Ta commande est repassée à l'état « Reçue »."],
        'in_progress' => ['Ta commande est en cours',              'Bonne nouvelle : nous avons commencé à travailler sur ta commande.'],
        'delivered'   => ['Ta commande est livrée',                'Ta commande est terminée et livrée. Merci pour ta confiance !'],
        'cancelled'   => ['Ta commande a été annulée',             'Ta commande a été annulée.'],
    ];
    [$heading, $sentence] = $texts[$status] ?? ['Mise à jour de ta commande', 'Ta commande a été mise à jour.'];

    $body = '<p>Bonjour ' . mail_h($toName) . ',</p><p>' . mail_h($sentence) . '</p>'
          . '<p style="margin:16px 0;"><strong>' . mail_h($ref) . '</strong> — ' . mail_h($title)
          . '<br><span style="color:#5B655E;">' . mail_h($service) . '</span></p>'
          . email_quote($note);

    $link = SITE_URL . '/order.php?id=' . $orderId;
    $alt  = "Bonjour {$toName},\n\n{$sentence}\n{$ref} — {$title} ({$service})\n"
          . ($note !== '' ? "\nMessage de Softener Lab :\n{$note}\n" : '')
          . "\nVoir ma commande : {$link}";

    return send_html_email(
        $toEmail, $toName,
        "[Softener Lab] {$ref} — {$heading}",
        email_layout($heading, $body, ['Voir ma commande', $link]),
        $alt
    );
}

/** Email au membre quand sa demande de formation est acceptée ('paid') ou refusée ('rejected'). */
function send_training_status_email(string $toEmail, string $toName, string $trainingName, string $status): bool
{
    if ($status === 'paid') {
        $heading  = 'Ton accès est activé';
        $sentence = "Ta demande a été acceptée : tu as maintenant accès à la formation « {$trainingName} ».";
        $button   = ['Ouvrir mon espace', SITE_URL . '/dashboard.php'];
    } else {
        $heading  = "Ta demande n'a pas pu être acceptée";
        $sentence = "Nous n'avons pas pu donner suite à ta demande pour la formation « {$trainingName} » pour le moment. "
                  . "N'hésite pas à nous écrire pour en discuter.";
        $button   = ['Nous contacter', SITE_URL . '/contact.html'];
    }
    $body = '<p>Bonjour ' . mail_h($toName) . ',</p><p>' . mail_h($sentence) . '</p>';
    $alt  = "Bonjour {$toName},\n\n{$sentence}\n\n{$button[0]} : {$button[1]}";

    return send_html_email(
        $toEmail, $toName,
        "[Softener Lab] {$heading}",
        email_layout($heading, $body, $button),
        $alt
    );
}

/** Email au membre juste après le dépôt d'une commande (accusé de réception). */
function send_order_received_email(string $toEmail, string $toName, int $orderId, string $ref, string $title, string $service): bool
{
    $heading = 'Ta commande est bien enregistrée';
    $sentence = 'Merci ! Nous avons bien reçu ta commande et revenons vers toi rapidement.';
    $body = '<p>Bonjour ' . mail_h($toName) . ',</p><p>' . mail_h($sentence) . '</p>'
          . '<p style="margin:16px 0;"><strong>' . mail_h($ref) . '</strong> — ' . mail_h($title)
          . '<br><span style="color:#5B655E;">' . mail_h($service) . '</span></p>';

    $link = SITE_URL . '/order.php?id=' . $orderId;
    $alt  = "Bonjour {$toName},\n\n{$sentence}\n{$ref} — {$title} ({$service})\n\nVoir ma commande : {$link}";

    return send_html_email(
        $toEmail, $toName,
        "[Softener Lab] {$ref} — Commande bien reçue",
        email_layout($heading, $body, ['Voir ma commande', $link]),
        $alt
    );
}

/** Email au membre juste après une demande d'accès à une formation (accusé de réception). */
function send_training_requested_email(string $toEmail, string $toName, string $trainingName): bool
{
    $heading = 'Ta demande est bien enregistrée';
    $sentence = "Merci ! Nous avons bien reçu ta demande d'accès à la formation « {$trainingName} » et revenons vers toi rapidement.";
    $body = '<p>Bonjour ' . mail_h($toName) . ',</p><p>' . mail_h($sentence) . '</p>';
    $link = SITE_URL . '/dashboard.php';
    $alt  = "Bonjour {$toName},\n\n{$sentence}\n\nMon espace : {$link}";

    return send_html_email(
        $toEmail, $toName,
        '[Softener Lab] Demande bien reçue — ' . $trainingName,
        email_layout($heading, $body, ['Ouvrir mon espace', $link]),
        $alt
    );
}

/** Prévient l'équipe (boîte d'envoi du site) d'une nouvelle commande. Le membre est en Reply-To. */
function notify_admin_new_order(string $memberName, string $memberEmail, int $orderId, string $ref, string $service, string $title, string $brief, ?string $deadline): bool
{
    $link = SITE_URL . '/admin-order.php?id=' . $orderId;
    $body = '<p><strong>' . mail_h($memberName) . '</strong> (' . mail_h($memberEmail) . ') vient de passer une commande.</p>'
          . '<p style="margin:16px 0;"><strong>' . mail_h($ref) . '</strong> — ' . mail_h($title)
          . '<br><span style="color:#5B655E;">' . mail_h($service)
          . ($deadline ? ' · échéance souhaitée : ' . mail_h($deadline) : '') . '</span></p>'
          . '<div style="padding:12px 16px;background:#F4F5F3;">' . nl2br(mail_h($brief)) . '</div>';
    $alt = "{$memberName} ({$memberEmail}) a passé la commande {$ref} — {$title} ({$service}).\n\n{$brief}\n\nGérer : {$link}";

    return send_html_email(
        SMTP_FROM, SMTP_FROM_NAME,
        "[Softener Lab] Nouvelle commande {$ref} — {$service}",
        email_layout('Nouvelle commande', $body, ['Gérer la commande', $link], 'Notification automatique.'),
        $alt,
        [$memberEmail, $memberName]
    );
}

/** Prévient l'équipe d'une nouvelle demande d'accès à une formation. */
function notify_admin_training_request(string $memberName, string $memberEmail, string $trainingName): bool
{
    $link = SITE_URL . '/admin-trainings.php';
    $body = '<p><strong>' . mail_h($memberName) . '</strong> (' . mail_h($memberEmail) . ') demande l\'accès à la formation '
          . '<strong>« ' . mail_h($trainingName) . ' »</strong>.</p>';
    $alt = "{$memberName} ({$memberEmail}) demande l'accès à la formation « {$trainingName} ».\n\nGérer : {$link}";

    return send_html_email(
        SMTP_FROM, SMTP_FROM_NAME,
        "[Softener Lab] Demande de formation — {$trainingName}",
        email_layout('Demande de formation', $body, ['Gérer les demandes', $link], 'Notification automatique.'),
        $alt,
        [$memberEmail, $memberName]
    );
}
