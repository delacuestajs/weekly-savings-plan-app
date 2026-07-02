<?php

require_once __DIR__ . '/../models/User.php';

class Auth
{
    private static $MAX_LOGIN_ATTEMPTS = 5;
    private static $LOCKOUT_DURATION = 900; // 15 minutes in seconds

    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Secure session settings
            @ini_set('session.cookie_httponly', 1);
            @ini_set('session.cookie_secure', 1);
            @ini_set('session.cookie_samesite', 'Lax');
            @ini_set('session.use_strict_mode', 1);
            @ini_set('session.gc_maxlifetime', 1800); // 30 minutes
            @session_start();
        } elseif (session_status() === PHP_SESSION_NONE && headers_sent()) {
            // Headers already sent, try to start session without ini_set
            @session_start();
        }
    }

    public static function login($user, $bagId = null)
    {
        self::startSession();
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['bag_id'] = $bagId ?? $user['bag_id'] ?? null;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        // Reset login attempts on successful login
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_until']);
    }

    public static function logout()
    {
        self::startSession();
        session_destroy();
        $_SESSION = [];
    }

    public static function isLoggedIn()
    {
        self::startSession();
        
        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > 1800) {
                self::logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
        }
        
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function isAdmin()
    {
        self::startSession();
        // Superadmin (3) and Admin (2) both have admin access
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] >= 2;
    }

    public static function isSuperAdmin()
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3;
    }

    public static function getUserId()
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserName()
    {
        self::startSession();
        return $_SESSION['user_name'] ?? '';
    }

    public static function getBagId()
    {
        self::startSession();
        return $_SESSION['bag_id'] ?? null;
    }

    public static function getUserRole()
    {
        self::startSession();
        return $_SESSION['user_role'] ?? 0;
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: index.php?toast=error&message=' . urlencode(Locale::get('login_required')));
            exit;
        }
    }

    public static function requireAdmin()
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: index.php?toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }
    }

    public static function requireSuperAdmin()
    {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            header('Location: index.php?toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }
    }

    // CSRF Protection
    public static function getCsrfToken()
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf()
    {
        self::startSession();
        if (empty($_POST['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
    }

    public static function csrfField()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getCsrfToken()) . '">';
    }

    // Rate Limiting
    public static function checkRateLimit($identifier)
    {
        self::startSession();
        
        // Check if locked out
        if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
            $remaining = $_SESSION['lockout_until'] - time();
            return [
                'allowed' => false,
                'message' => sprintf(Locale::get('too_many_attempts'), ceil($remaining / 60))
            ];
        }
        
        // Reset lockout if expired
        if (isset($_SESSION['lockout_until']) && time() >= $_SESSION['lockout_until']) {
            unset($_SESSION['lockout_until']);
            unset($_SESSION['login_attempts']);
        }
        
        return ['allowed' => true];
    }

    public static function recordFailedAttempt()
    {
        self::startSession();
        
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        
        $_SESSION['login_attempts']++;
        
        if ($_SESSION['login_attempts'] >= self::$MAX_LOGIN_ATTEMPTS) {
            $_SESSION['lockout_until'] = time() + self::$LOCKOUT_DURATION;
        }
    }

    public static function resetRateLimit()
    {
        self::startSession();
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_until']);
    }
}
