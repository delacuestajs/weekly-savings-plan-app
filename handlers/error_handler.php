<?php

/**
 * Global error handler - catches all errors/exceptions and shows a user-friendly modal.
 * Logs errors to PHP error log and activity_logs table.
 */

// Prevent double registration
if (defined('ERROR_HANDLER_REGISTERED')) return;
define('ERROR_HANDLER_REGISTERED', true);

require_once __DIR__ . '/../models/ActivityLog.php';

// Store error for output in the modal
$GLOBALS['__app_error'] = null;

function app_error_handler($errno, $errstr, $errfile, $errline)
{
    // Don't suppress errors with @
    if (!(error_reporting() & $errno)) return false;

    $GLOBALS['__app_error'] = [
        'type' => 'Error',
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'code' => $errno,
    ];

    app_log_error($errstr, $errfile, $errline, $errno);
    app_show_error_modal();
    exit;
}

function app_exception_handler($exception)
{
    $GLOBALS['__app_error'] = [
        'type' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'code' => $exception->getCode(),
    ];

    app_log_error($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getCode());
    app_show_error_modal();
    exit;
}

function app_shutdown_handler()
{
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $GLOBALS['__app_error'] = [
            'type' => 'Fatal Error',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'code' => $error['type'],
        ];

        app_log_error($error['message'], $error['file'], $error['line'], $error['type']);
        app_show_error_modal();
    }
}

function app_log_error($message, $file, $line, $code = 0)
{
    // PHP error log
    $logEntry = sprintf("[%s] %s in %s on line %d (code %s)\n",
        date('Y-m-d H:i:s'), $message, $file, $line, $code);
    error_log($logEntry);

    // activity_logs table via ActivityLog model
    try {
        if (class_exists('ActivityLog')) {
            $userId = $_SESSION['user_id'] ?? null;
            $userName = (($_SESSION['firstname'] ?? 'Unknown') . ' ' . ($_SESSION['lastname'] ?? ''));

            ActivityLog::log('system_error', null, null, [
                'message' => $message,
                'file' => basename($file),
                'line' => $line,
                'code' => $code,
            ], null, $userId, $userName);
        }
    } catch (\Exception $e) {
        // Silently fail - don't cause another error
    }
}

function app_show_error_modal()
{
    $error = $GLOBALS['__app_error'] ?? null;
    $errorMsg = htmlspecialchars($error['message'] ?? 'Unknown error', ENT_QUOTES);
    $errorFile = htmlspecialchars($error['file'] ?? '', ENT_QUOTES);
    $errorLine = htmlspecialchars($error['line'] ?? '', ENT_QUOTES);
    $errorType = htmlspecialchars($error['type'] ?? 'Error', ENT_QUOTES);

    $details = "Type: {$errorType}\nMessage: {$errorMsg}\nFile: {$errorFile}\nLine: {$errorLine}";

    // Load translations based on lang cookie
    $lang = $_COOKIE['lang'] ?? 'en';
    $langFile = __DIR__ . '/../lang/' . $lang . '.php';
    $t = [];
    if (file_exists($langFile)) {
        $t = require $langFile;
    }

    $title = $lang === 'es' ? 'Error' : 'Error';
    $heading = $t['error_heading'] ?? ($lang === 'es' ? 'Ups, algo salió mal' : 'Oops, something went wrong');
    $message = $t['error_message'] ?? ($lang === 'es' ? 'Ocurrió un error inesperado. Nuestro equipo ha sido notificado. Por favor, inténtelo de nuevo más tarde.' : 'An unexpected error occurred. Our team has been notified. Please try again later.');
    $btnDetails = $t['error_show_details'] ?? ($lang === 'es' ? 'Ver Detalles' : 'Show Details');
    $btnAccept = $t['error_accept'] ?? ($lang === 'es' ? 'Aceptar' : 'Accept');

    // If response hasn't started, send proper headers
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
    }

    // Output the full page with error modal
    echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $title . '</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .error-container { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); padding: 40px; max-width: 480px; width: 90%; text-align: center; }
    .error-icon { width: 64px; height: 64px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    .error-icon svg { width: 32px; height: 32px; color: #dc2626; }
    h1 { font-size: 20px; color: #111827; margin-bottom: 8px; }
    p { color: #6b7280; font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
    .btn-details { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; }
    .btn-details:hover { background: #e5e7eb; }
    .btn-accept { background: #2563eb; color: white; border: none; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s; margin-top: 20px; }
    .btn-accept:hover { background: #1d4ed8; }
    .details-box { display: none; margin-top: 16px; text-align: left; background: #1f2937; color: #d1d5db; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 12px; line-height: 1.6; white-space: pre-wrap; word-break: break-all; max-height: 200px; overflow-y: auto; }
    .details-box.show { display: block; }
</style>
</head>
<body>
<div class="error-container">
    <div class="error-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <h1>' . $heading . '</h1>
    <p>' . $message . '</p>
    <button class="btn-details" onclick="document.getElementById(\'errorDetails\').classList.toggle(\'show\')">' . $btnDetails . '</button>
    <div id="errorDetails" class="details-box">' . $details . '</div>
    <button class="btn-accept" onclick="window.location.href=\'index.php\'">' . $btnAccept . '</button>
</div>
</body>
</html>';
}

set_error_handler('app_error_handler');
set_exception_handler('app_exception_handler');
register_shutdown_function('app_shutdown_handler');
