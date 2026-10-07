<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Envoi des emails (SMTP)
 * =============================================================================
 *
 *  Utilise PHPMailer en SMTP si la dépendance Composer est présente,
 *  avec repli sur mail() uniquement si PHPMailer est indisponible.
 *  Les identifiants SMTP proviennent exclusivement du fichier .env.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

/**
 * Indique si PHPMailer est disponible (installé via Composer).
 */
function mailer_available(): bool
{
    if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
        return true;
    }
    $autoload = ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (is_readable($autoload)) {
        require_once $autoload;
    }
    return class_exists('\\PHPMailer\\PHPMailer\\PHPMailer');
}

/**
 * Construit (si possible) une instance PHPMailer configurée en SMTP.
 *
 * @return object|null
 */
function build_mailer(): ?object
{
    if (!mailer_available()) {
        return null;
    }

    $host = (string) env('SMTP_HOST', '');
    if ($host === '') {
        return null;
    }

    $mailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
    $mail = new $mailerClass(true);
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->Port = (int) env('SMTP_PORT', 587);
    $mail->SMTPAuth = true;
    $mail->Username = (string) env('SMTP_USERNAME', '');
    $mail->Password = (string) env('SMTP_PASSWORD', '');
    // Délai SMTP court : si le serveur mail ne répond pas, le visiteur n'attend pas 5 min (défaut PHPMailer).
    $mail->Timeout = 10;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $encryption = strtolower((string) env('SMTP_ENCRYPTION', 'tls'));
    if ($encryption === 'ssl' || $encryption === 'smtps') {
        $mail->SMTPSecure = defined($mailerClass . '::ENCRYPTION_SMTPS') ? constant($mailerClass . '::ENCRYPTION_SMTPS') : 'ssl';
    } elseif ($encryption === 'tls' || $encryption === 'starttls') {
        $mail->SMTPSecure = defined($mailerClass . '::ENCRYPTION_STARTTLS') ? constant($mailerClass . '::ENCRYPTION_STARTTLS') : 'tls';
    } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    }

    $from = (string) env('MAIL_FROM', '');
    $fromName = (string) env('MAIL_FROM_NAME', 'Portfolio SKS');
    if ($from === '') {
        $from = (string) env('SMTP_USERNAME', 'no-reply@localhost');
    }
    $mail->setFrom($from, $fromName);

    return $mail;
}

/**
 * Envoie un email en HTML. Retourne true en cas de succès.
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $subject
 * @param string $htmlBody
 * @param string $textBody
 * @param string|null $replyToEmail
 * @param string|null $replyToName
 * @return bool
 */
function send_mail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody, ?string $replyToEmail = null, ?string $replyToName = null): bool
{
    $mail = build_mailer();

    try {
        if ($mail !== null) {
            $mail->clearAllRecipients();
            $mail->addAddress($toEmail, $toName);
            if ($replyToEmail !== null && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL) !== false) {
                $mail->addReplyTo($replyToEmail, (string) $replyToName);
            }
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;
            return $mail->send();
        }

        // Repli : fonction mail() native (si aucun SMTP n'est configuré).
        $from = (string) env('MAIL_FROM', (string) env('SMTP_USERNAME', 'no-reply@localhost'));
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . (string) env('MAIL_FROM_NAME', 'Portfolio SKS') . ' <' . $from . '>',
        ];
        if ($replyToEmail !== null && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $headers[] = 'Reply-To: ' . $replyToEmail;
        }
        return @mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
    } catch (Throwable $e) {
        log_event('error', 'Email sending failed', ['exception' => $e->getMessage()]);
        return false;
    }
}

/**
 * Notifie l'administrateur de la réception d'un nouveau message.
 *
 * @param array<string,string> $data
 */
function send_admin_notification(array $data): bool
{
    $lang = normalize_lang($data['language'] ?? 'fr');
    $to = (string) env('MAIL_TO', (string) env('ADMIN_EMAIL', ''));
    $subjectLine = ($data['subject'] !== '' ? $data['subject'] : ($lang === 'en' ? 'New inquiry' : 'Prise de contact'));
    $subject = 'Nouveau message depuis le portfolio - ' . $subjectLine;

    $html = email_template(
        'Nouveau message depuis le portfolio',
        [
            'Nom' => $data['name'],
            'Email' => $data['email'],
            'Téléphone' => $data['phone'] !== '' ? $data['phone'] : '—',
            'Sujet' => $data['subject'] !== '' ? $data['subject'] : '—',
            'Type de demande' => $data['collab_type'] !== '' ? $data['collab_type'] : '—',
            'Langue' => strtoupper($lang),
            'Date' => date('Y-m-d H:i:s'),
            'Message' => $data['message'],
        ],
        true,
        $data['email']
    );

    $text = "Nouveau message depuis le portfolio\n\n"
        . 'Nom : ' . $data['name'] . "\n"
        . 'Email : ' . $data['email'] . "\n"
        . 'Téléphone : ' . ($data['phone'] !== '' ? $data['phone'] : '—') . "\n"
        . 'Sujet : ' . ($data['subject'] !== '' ? $data['subject'] : '—') . "\n"
        . 'Type de demande : ' . ($data['collab_type'] !== '' ? $data['collab_type'] : '—') . "\n"
        . 'Langue : ' . strtoupper($lang) . "\n"
        . 'Date : ' . date('Y-m-d H:i:s') . "\n\n"
        . $data['message'] . "\n";

    return send_mail($to, 'Portfolio SKS', $subject, $html, $text, $data['email'], $data['name']);
}

/**
 * Envoie au visiteur un email de confirmation, dans sa langue.
 *
 * @param array<string,string> $data
 */
function send_visitor_confirmation(array $data): bool
{
    $lang = normalize_lang($data['language'] ?? 'fr');

    if ($lang === 'en') {
        $subject = 'Thank you for your message — SOADAN Koffi Sylvain';
        $intro = 'Thank you for your message. Your message has been received. '
            . 'I will get back to you as soon as possible.';
    } else {
        $subject = 'Merci pour votre message — SOADAN Koffi Sylvain';
        $intro = 'Merci pour votre message. Votre demande a bien été reçue. '
            . 'Je reviendrai vers vous dès que possible.';
    }

    $html = email_template(
        $lang === 'en' ? 'Message received' : 'Message reçu',
        ['Message' => $intro],
        false
    );
    $text = $intro . "\n";

    return send_mail(
        $data['email'],
        $data['name'],
        $subject,
        $html,
        $text,
        (string) env('MAIL_FROM', ''),
        (string) env('MAIL_FROM_NAME', 'SOADAN Koffi Sylvain')
    );
}

/**
 * Génère un gabarit email HTML sobre (aligné sur la charte du portfolio).
 *
 * @param array<string,string> $fields
 */
function email_template(string $title, array $fields, bool $showReplyButton = false, string $replyEmail = ''): string
{
    $rows = '';
    foreach ($fields as $label => $value) {
        $rows .= '<tr>'
            . '<td style="padding:6px 12px;border-bottom:1px solid #dfe3e8;font-weight:bold;color:#0a243b;vertical-align:top;white-space:nowrap">'
            . e($label) . '</td>'
            . '<td style="padding:6px 12px;border-bottom:1px solid #dfe3e8;color:#17202b">'
            . nl2br(e($value)) . '</td>'
            . '</tr>';
    }

    $reply = '';
    if ($showReplyButton && $replyEmail !== '' && filter_var($replyEmail, FILTER_VALIDATE_EMAIL) !== false) {
        $reply = '<p style="margin:20px 0 0">'
            . '<a href="mailto:' . e($replyEmail) . '" '
            . 'style="background:#0f6b3c;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:4px;display:inline-block">'
            . 'Répondre au visiteur</a></p>';
    }

    return '<!doctype html><html><body style="margin:0;background:#fbfaf7;font-family:Times New Roman,serif;color:#17202b">'
        . '<div style="max-width:640px;margin:0 auto;padding:24px">'
        . '<h1 style="font-size:20px;color:#0a243b;border-bottom:2px solid #0a243b;padding-bottom:8px">'
        . e($title) . '</h1>'
        . '<table style="width:100%;border-collapse:collapse;font-size:15px">' . $rows . '</table>'
        . $reply
        . '<p style="margin-top:24px;font-size:12px;color:#5a6675">'
        . '© 2026 Sylvain Koffi SOADAN — African Digital Governance &amp; AI Policy</p>'
        . '</div></body></html>';
}
