<?php
/**
 * Background email sender - called via exec() from Mail::sendAsync()
 * Usage: php send_async.php <to> <subject> <body_file>
 */
if ($argc < 4) exit(1);

$to = $argv[1];
$subject = $argv[2];
$bodyFile = $argv[3];

if (!file_exists($bodyFile)) exit(1);

$body = file_get_contents($bodyFile);
@unlink($bodyFile);

if (empty($to) || empty($body)) exit(1);

require __DIR__ . '/mail.php';
Mail::send($to, $subject, $body);
exit(0);
