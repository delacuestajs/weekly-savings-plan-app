<?php

require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../controllers/Auth.php';

class LogController
{
    private $log;

    public function __construct()
    {
        $this->log = new ActivityLog();
    }

    public function index()
    {
        Auth::requireAdmin();

        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
        ];

        $bagId = Auth::getBagId();
        $logs = $this->log->getAll($filters, $bagId);
        $actions = $this->log->getDistinctActions($bagId);
        $users = $this->log->getDistinctUsers($bagId);

        require __DIR__ . '/../views/logs/list.php';
    }
}
