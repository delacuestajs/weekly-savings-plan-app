<?php

require_once __DIR__ . '/../models/User.php';

class Auth
{
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user)
    {
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
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
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function isAdmin()
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2;
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
}
