<?php

class Mail
{
    public static function send($to, $subject, $htmlBody, $textBody = null)
    {
        $from = getenv('SMTP_FROM') ?: '';
        if (empty($from) || empty($to)) {
            return false;
        }

        $headers = "From: {$from}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($textBody) {
            $boundary = md5(uniqid(time()));
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= $textBody . "\r\n\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $htmlBody . "\r\n\r\n";
            $body .= "--{$boundary}--";
        } else {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body = $htmlBody;
        }

        return @mail($to, $subject, $body, $headers);
    }
}
