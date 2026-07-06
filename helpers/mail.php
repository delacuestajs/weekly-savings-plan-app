<?php

class Mail
{
    private static function getConfig()
    {
        $envFile = __DIR__ . '/../.env';
        $env = [];

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $env[trim($key)] = trim($value);
                }
            }
        }

        return [
            'host' => getenv('SMTP_HOST') ?: ($env['SMTP_HOST'] ?? ''),
            'port' => getenv('SMTP_PORT') ?: ($env['SMTP_PORT'] ?? '587'),
            'user' => getenv('SMTP_USER') ?: ($env['SMTP_USER'] ?? ''),
            'pass' => getenv('SMTP_PASS') ?: ($env['SMTP_PASS'] ?? ''),
            'from' => getenv('SMTP_FROM') ?: ($env['SMTP_FROM'] ?? ''),
        ];
    }

    public static function send($to, $subject, $htmlBody, $textBody = null)
    {
        $config = self::getConfig();
        $from = $config['from'];

        if (empty($from) || empty($to)) {
            return false;
        }

        $headers = "From: {$from}\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($textBody) {
            $boundary = md5(uniqid(time()));
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $headers .= "\r\n";

            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= $textBody . "\r\n\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $htmlBody . "\r\n\r\n";
            $body .= "--{$boundary}--";
        } else {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";
            $body = $htmlBody;
        }

        $message = $headers . $body;

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open('/usr/bin/msmtp -t', $descriptors, $pipes);

        if (!is_resource($process)) {
            error_log('Mail: failed to open msmtp process');
            return false;
        }

        fwrite($pipes[0], $message);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            error_log("Mail: msmtp exit {$exitCode} | stdout: {$stdout} | stderr: {$stderr}");
            return false;
        }

        return true;
    }
}
