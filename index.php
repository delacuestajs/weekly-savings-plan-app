<?php

require_once __DIR__ . '/locale.php';
require_once __DIR__ . '/controllers/SavingController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ActivityController.php';

if (isset($_GET['lang'])) {
    Locale::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$module = $_GET['module'] ?? 'saving';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

if ($module === 'user') {
    $controller = new UserController();
    
    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
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
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
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
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
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
