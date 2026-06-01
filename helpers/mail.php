<?php

require_once __DIR__ . '/../vendor/autoload.php';

function email_html_template(string $body_html): string
{
    return '<!DOCTYPE html>' .
    '<html><head><meta charset="UTF-8"></head>' .
    '<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">' .
    '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' .
    '<tr><td align="center" style="padding:30px 10px;">' .
    '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1);">' .
    '<tr><td style="background:linear-gradient(135deg,#2563eb,#1e40af);padding:30px;text-align:center;">' .
    '<h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;">Smart Mall</h1>' .
    '</td></tr>' .
    '<tr><td style="padding:30px;color:#333;font-size:15px;line-height:1.6;">' .
    $body_html .
    '</td></tr>' .
    '<tr><td style="background:#f9fafb;padding:20px;text-align:center;color:#888;font-size:12px;border-top:1px solid #eee;">' .
    '&copy; 2026 Smart Mall. All rights reserved.' .
    '</td></tr></table></td></tr></table></body></html>';
}

function send_mail(string $to, string $subject, string $body, ?string $from = null, ?string $log_id = null, ?string $html_body = null): bool
{
    $mail_dir = __DIR__ . '/../mail';
    if (!is_dir($mail_dir)) {
        @mkdir($mail_dir, 0755, true);
    }

    $from  = $from ?? ($_ENV['SMTP_FROM'] ?? 'noreply@smartmall.com');

    $app_env = $_ENV['APP_ENV'] ?? 'production';
    if ($app_env === 'development') {
        _mail_log($to, $subject, $body, $from, $mail_dir, $log_id, $html_body);
        return true;
    }

    $api_key = $_ENV['BREVO_API_KEY'] ?? '';

    if (empty($api_key)) {
        error_log('send_mail: BREVO_API_KEY not configured in .env');
        _mail_log($to, $subject, $body, $from, $mail_dir, $log_id, $html_body);
        return false;
    }

    try {
        $brevo = new \Brevo\Brevo($api_key);

        $html = $html_body ?? nl2br(e($body));

        $request = new \Brevo\TransactionalEmails\Requests\SendTransacEmailRequest([
            'sender' => new \Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender([
                'email' => $from,
                'name'  => 'SmartMall',
            ]),
            'to' => [
                new \Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem([
                    'email' => $to,
                ]),
            ],
            'subject'     => $subject,
            'htmlContent' => $html,
            'textContent' => $body,
        ]);

        $response = $brevo->getTransactionalEmails()->sendTransacEmail($request);

        $message_id = '';
        if ($response && property_exists($response, 'messageId')) {
            $message_id = $response->messageId;
        }

        error_log("send_mail: sent to $to — messageId: " . ($message_id ?: 'unknown'));
        return true;

    } catch (\Throwable $e) {
        error_log('send_mail: API error — ' . $e->getMessage());
        _mail_log($to, $subject, $body, $from, $mail_dir, $log_id, $html_body);
        return false;
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function _mail_log(string $to, string $subject, string $body, string $from, string $dir, ?string $log_id = null, ?string $html_body = null): void
{
    if (!is_dir($dir) || !is_writable($dir)) {
        error_log("send_mail: cannot log to $dir — not writable");
        return;
    }
    $ts = date('Y-m-d_H-i-s');
    $id = $log_id ?? substr(bin2hex(random_bytes(4)), 0, 8);

    $boundary = 'boundary_' . bin2hex(random_bytes(8));
    $eml = "From: $from\r\nTo: $to\r\nSubject: $subject\r\n";
    $eml .= "MIME-Version: 1.0\r\n";
    $eml .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
    $eml .= "--$boundary\r\n";
    $eml .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n$body\r\n\r\n";
    if ($html_body !== null) {
        $eml .= "--$boundary\r\n";
        $eml .= "Content-Type: text/html; charset=UTF-8\r\n\r\n$html_body\r\n\r\n";
    }
    $eml .= "--$boundary--";

    @file_put_contents("$dir/{$ts}_{$id}.eml", $eml);
}
