<?php

require_once __DIR__ . '/locale.php';
require_once __DIR__ . '/controllers/Auth.php';
require_once __DIR__ . '/controllers/SavingController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ActivityController.php';
require_once __DIR__ . '/controllers/LogController.php';

Auth::startSession();

if (isset($_GET['lang'])) {
    Locale::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$module = $_GET['module'] ?? 'saving';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

if ($action === 'login') {
    $user = new User();
    $userData = $user->authenticate($_POST['username'], $_POST['password']);
    if ($userData) {
        Auth::login($userData);
        ActivityLog::log('user_login', $userData['id'], $userData['firstname'] . ' ' . $userData['lastname']);
        if (password_verify('password', $userData['password'])) {
            $_SESSION['show_password_change'] = true;
        }
        header('Location: index.php?toast=success&message=' . urlencode(Locale::get('login_success')));
        exit;
    }
    ActivityLog::log('login_failed', null, $_POST['username'] ?? 'unknown');
    header('Location: index.php?toast=error&message=' . urlencode(Locale::get('login_failed')));
    exit;
}

if ($action === 'logout') {
    $userId = Auth::getUserId();
    $userName = Auth::getUserName();
    Auth::logout();
    ActivityLog::log('user_logout', $userId, $userName);
    header('Location: index.php?toast=success&message=' . urlencode(Locale::get('logout_success')));
    exit;
}

if ($action === 'change_password') {
    $user = new User();
    $userId = Auth::getUserId();
    $userData = $user->getById($userId);
    $isForced = isset($_POST['is_forced_change']) && $_POST['is_forced_change'] === '1';
    
    if (!$isForced) {
        if (!$userData || !password_verify($_POST['current_password'], $userData['password'])) {
            header('Location: index.php?toast=error&message=' . urlencode(Locale::get('current_password_incorrect')));
            exit;
        }
    }
    
    if ($user->changePassword($userId, $_POST['new_password'])) {
        unset($_SESSION['show_password_change']);
        header('Location: index.php?toast=success&message=' . urlencode(Locale::get('password_changed_successfully')));
        exit;
    }
    header('Location: index.php?toast=error&message=' . urlencode(Locale::get('error_changing_password')));
    exit;
}

if (!Auth::isLoggedIn()) {
    require __DIR__ . '/views/login.php';
    exit;
}

if ($module === 'user') {
    $controller = new UserController();
    
    switch ($action) {
        case 'create':
            Auth::requireAdmin();
            $controller->create();
            break;
        case 'store':
            Auth::requireAdmin();
            $controller->store();
            break;
        case 'edit':
            Auth::requireAdmin();
            $controller->edit($id);
            break;
        case 'update':
            Auth::requireAdmin();
            $controller->update($id);
            break;
        case 'reset_password':
            Auth::requireAdmin();
            $controller->resetPassword($id);
            break;
        case 'delete':
            Auth::requireAdmin();
            $controller->delete($id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($module === 'activity') {
    $controller = new ActivityController();
    
    switch ($action) {
        case 'create':
            Auth::requireAdmin();
            $controller->create();
            break;
        case 'store':
            Auth::requireAdmin();
            $controller->store();
            break;
        case 'edit':
            Auth::requireAdmin();
            $controller->edit($id);
            break;
        case 'update':
            Auth::requireAdmin();
            $controller->update($id);
            break;
        case 'delete':
            Auth::requireAdmin();
            $controller->delete($id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($module === 'log') {
    $controller = new LogController();
    $controller->index();
} else {
    $controller = new SavingController();
    
    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            Auth::requireAdmin();
            $controller->edit($id);
            break;
        case 'update':
            Auth::requireAdmin();
            $controller->update($id);
            break;
        case 'delete':
            Auth::requireAdmin();
            $controller->delete($id);
            break;
        case 'verify':
            Auth::requireAdmin();
            $controller->verify($id);
            break;
        case 'payments':
            $controller->index();
            break;
        case 'weekly':
            $controller->weekly();
            break;
        default:
            $controller->weekly();
            break;
    }
}
