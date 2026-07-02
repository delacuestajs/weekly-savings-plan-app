<?php

require_once __DIR__ . '/locale.php';
require_once __DIR__ . '/controllers/Auth.php';
require_once __DIR__ . '/controllers/SavingController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ActivityController.php';
require_once __DIR__ . '/controllers/ExpenseController.php';
require_once __DIR__ . '/controllers/LogController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/BagController.php';

Auth::startSession();

if (isset($_GET['lang'])) {
    Locale::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$module = $_GET['module'] ?? 'saving';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Login action with rate limiting
if ($action === 'login') {
    $rateCheck = Auth::checkRateLimit($_SERVER['REMOTE_ADDR']);
    if (!$rateCheck['allowed']) {
        header('Location: index.php?toast=error&message=' . urlencode($rateCheck['message']));
        exit;
    }
    
    $bagId = !empty($_POST['bag_id']) ? (int)$_POST['bag_id'] : null;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = new User();
    $userData = $user->authenticate($username, $password, $bagId);
    if ($userData) {
        Auth::login($userData, $bagId);
        ActivityLog::log('user_login', $userData['id'], $userData['firstname'] . ' ' . $userData['lastname']);
        if (User::isDefaultPassword($userData['password'])) {
            $_SESSION['show_password_change'] = true;
        }
        header('Location: index.php?toast=success&message=' . urlencode(Locale::get('login_success')));
        exit;
    }
    
    Auth::recordFailedAttempt();
    ActivityLog::log('login_failed', null, $_POST['username'] ?? 'unknown');
    header('Location: index.php?toast=error&message=' . urlencode(Locale::get('login_failed')));
    exit;
}

// Logout action
if ($action === 'logout') {
    $userId = Auth::getUserId();
    $userName = Auth::getUserName();
    Auth::logout();
    ActivityLog::log('user_logout', null, null, null, null, $userId, $userName);
    header('Location: index.php?toast=success&message=' . urlencode(Locale::get('logout_success')));
    exit;
}

// Password change with CSRF validation
if ($action === 'change_password') {
    if (!Auth::validateCsrf()) {
        ActivityLog::log('csrf_validation_failed', Auth::getUserId(), Auth::getUserName(), 
            ['action' => 'change_password']);
        header('Location: index.php?toast=error&message=' . urlencode(Locale::get('invalid_request')));
        exit;
    }
    
    $user = new User();
    $userId = Auth::getUserId();
    $userData = $user->getById($userId);
    $isForced = isset($_SESSION['show_password_change']) && $_SESSION['show_password_change'] && 
                isset($_POST['is_forced_change']) && $_POST['is_forced_change'] === '1';
    
    if (!$isForced) {
        if (!$userData || !password_verify($_POST['current_password'], $userData['password'])) {
            header('Location: index.php?toast=error&message=' . urlencode(Locale::get('current_password_incorrect')));
            exit;
        }
    }
    
    // Validate password complexity
    $newPassword = $_POST['new_password'] ?? '';
    if (strlen($newPassword) < 8) {
        header('Location: index.php?toast=error&message=' . urlencode(Locale::get('password_too_short')));
        exit;
    }
    
    if ($user->changePassword($userId, $newPassword)) {
        unset($_SESSION['show_password_change']);
        ActivityLog::log('password_changed', $userId);
        header('Location: index.php?toast=success&message=' . urlencode(Locale::get('password_changed_successfully')));
        exit;
    }
    header('Location: index.php?toast=error&message=' . urlencode(Locale::get('error_changing_password')));
    exit;
}

// Profile update with CSRF validation
if ($action === 'update_profile') {
    Auth::requireLogin();
    if (!Auth::validateCsrf()) {
        ActivityLog::log('csrf_validation_failed', Auth::getUserId(), Auth::getUserName(), 
            ['action' => 'update_profile']);
        header('Location: index.php?toast=error&message=' . urlencode(Locale::get('invalid_request')));
        exit;
    }
    $controller = new UserController();
    $controller->updateProfile();
    exit;
}

// Require login for all other actions
if (!Auth::isLoggedIn()) {
    require __DIR__ . '/views/login.php';
    exit;
}

// CSRF validation for all POST requests (except login/logout which are handled above)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, ['login', 'logout'])) {
    if (!Auth::validateCsrf()) {
        ActivityLog::log('csrf_validation_failed', Auth::getUserId(), Auth::getUserName(), 
            ['action' => $action, 'module' => $module]);
        header('Location: index.php?toast=error&message=' . urlencode(Locale::get('invalid_request')));
        exit;
    }
}

if ($module === 'user') {
    $controller = new UserController();
    
    switch ($action) {
        case 'store':
            Auth::requireAdmin();
            $controller->store();
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
        case 'get_json':
            $controller->getJson($id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($module === 'activity') {
    $controller = new ActivityController();
    
    switch ($action) {
        case 'store':
            Auth::requireAdmin();
            $controller->store();
            break;
        case 'update':
            Auth::requireAdmin();
            $controller->update($id);
            break;
        case 'delete':
            Auth::requireAdmin();
            $controller->delete($id);
            break;
        case 'get_json':
            $controller->getJson($id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($module === 'expense') {
    $controller = new ExpenseController();
    
    switch ($action) {
        case 'store':
            Auth::requireAdmin();
            $controller->store();
            break;
        case 'get_json':
            Auth::requireAdmin();
            $controller->getJson($id);
            break;
        case 'edit':
            Auth::requireAdmin();
            $controller->edit($id);
            break;
        case 'update':
            Auth::requireAdmin();
            $controller->update($id);
            break;
        case 'confirm':
            Auth::requireAdmin();
            $controller->confirm($id);
            break;
        case 'delete':
            Auth::requireAdmin();
            $controller->delete($id);
            break;
        default:
            header('Location: index.php?module=activity');
            exit;
    }
} elseif ($module === 'log') {
    $controller = new LogController();
    $controller->index();
} elseif ($module === 'dashboard') {
    $controller = new DashboardController();
    $controller->index();
    } elseif ($module === 'bag') {
    $controller = new BagController();
    
    switch ($action) {
        case 'store':
            $controller->store();
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'disable':
            $controller->disable($id);
            break;
        case 'truncate':
            $controller->truncate($id);
            break;
        case 'download_dump':
            $controller->downloadDump();
            break;
        case 'get_json':
            $controller->getJson($id);
            break;
        default:
            $controller->index();
            break;
    }
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
        case 'get_json':
            $controller->getJson($id);
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
