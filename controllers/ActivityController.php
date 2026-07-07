<?php

require_once __DIR__ . '/../models/Activity.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class ActivityController
{
    private $activity;
    private $expense;

    public function __construct()
    {
        $this->activity = new Activity();
        $this->expense = new Expense();
    }

    public function index()
    {
        $bagId = Auth::getBagId();
        $activities = $this->activity->getAll($bagId);
        $expensesByActivity = [];
        
        // Load expenses for each activity
        while ($row = $activities->fetch(PDO::FETCH_ASSOC)) {
            $expensesByActivity[$row['id']] = [
                'activity' => $row,
                'expenses' => $this->expense->getByActivityId($row['id']),
                'total_expenses' => $this->expense->getTotalByActivityId($row['id'])
            ];
        }
        
        require __DIR__ . '/../views/activities/list.php';
    }

    public function store()
    {
        Auth::requireAdmin();

        $this->activity->name = trim($_POST['name'] ?? '');
        $this->activity->description = !empty($_POST['description']) ? trim($_POST['description']) : null;
        $this->activity->value = $_POST['value'];
        $this->activity->activity_date = $_POST['activity_date'];
        $this->activity->bag_id = Auth::getBagId();
        $this->activity->created_at = date('Y-m-d H:i:s');

        if ($this->activity->create()) {
            ActivityLog::log('activity_created', null, null,
                ['activity_id' => $this->activity->id, 'name' => $this->activity->name, 'value' => $this->activity->value]);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=success&message=' . urlencode(Locale::get('activity_created_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=error&message=' . urlencode(Locale::get('error_creating_activity')));
        exit;
    }

    public function update($id)
    {
        Auth::requireAdmin();

        $existingActivity = $this->activity->getById($id);
        if (!$existingActivity) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=error&message=' . urlencode(Locale::get('activity_not_found')));
            exit;
        }

        $this->activity->id = $id;
        $this->activity->name = trim($_POST['name'] ?? '');
        $this->activity->description = !empty($_POST['description']) ? trim($_POST['description']) : null;
        $this->activity->value = $_POST['value'];
        $this->activity->activity_date = $_POST['activity_date'];

        // Build changes
        $changes = [];
        if ($existingActivity['name'] !== $this->activity->name) {
            $changes['Name'] = ['old' => $existingActivity['name'], 'new' => $this->activity->name];
        }
        if (($existingActivity['description'] ?? '') !== ($this->activity->description ?? '')) {
            $changes['Description'] = ['old' => $existingActivity['description'] ?? '', 'new' => $this->activity->description ?? ''];
        }
        if ((float)$existingActivity['value'] !== (float)$this->activity->value) {
            $changes['Value'] = ['old' => $existingActivity['value'], 'new' => $this->activity->value];
        }
        if ($existingActivity['activity_date'] !== $this->activity->activity_date) {
            $changes['Date'] = ['old' => $existingActivity['activity_date'], 'new' => $this->activity->activity_date];
        }

        if ($this->activity->update()) {
            ActivityLog::log('activity_updated', null, null,
                ['activity_id' => $id, 'name' => $this->activity->name],
                !empty($changes) ? $changes : null);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=success&message=' . urlencode(Locale::get('activity_updated_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=error&message=' . urlencode(Locale::get('error_updating_activity')));
        exit;
    }

    public function delete($id)
    {
        Auth::requireAdmin();

        $activity = $this->activity->getById($id);
        if (!$activity) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=error&message=' . urlencode(Locale::get('activity_not_found')));
            exit;
        }

        if ($this->activity->delete($id)) {
            ActivityLog::log('activity_deleted', null, null,
                ['activity_id' => $id, 'name' => $activity['name']]);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=success&message=' . urlencode(Locale::get('activity_deleted_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=activity&toast=error&message=' . urlencode(Locale::get('error_deleting_activity')));
        exit;
    }

    public function getJson($id)
    {
        if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            exit;
        }

        header('Content-Type: application/json');

        $activity = $this->activity->getById($id);
        if (!$activity) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }

        echo json_encode($activity);
        exit;
    }
}
