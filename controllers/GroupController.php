<?php

require_once __DIR__ . '/../models/Group.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class GroupController
{
    private $group;

    public function __construct()
    {
        $this->group = new Group();
    }

    private function getReturnUrl()
    {
        return $_GET['return'] ?? $_POST['return'] ?? ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group';
    }

    public function index()
    {
        Auth::requireSuperAdmin();
        $groups = $this->group->getAllIncludingInactive();
        require __DIR__ . '/../views/groups/list.php';
    }

    public function create()
    {
        Auth::requireSuperAdmin();
        require __DIR__ . '/../views/groups/create.php';
    }

    public function store()
    {
        Auth::requireSuperAdmin();
        $returnUrl = $this->getReturnUrl();

        $this->group->name = $_POST['name'];
        $this->group->description = $_POST['description'] ?? null;
        $this->group->status = !empty($_POST['status']) ? (int)$_POST['status'] : 1;

        if ($this->group->isNameTaken($this->group->name)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&action=create&toast=error&message=' . urlencode(Locale::get('group_name_taken')));
            exit;
        }

        if ($this->group->create()) {
            ActivityLog::log('group_created', null, null,
                ['group_id' => $this->group->id, 'name' => $this->group->name]);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&action=create&toast=error&message=' . urlencode(Locale::get('error_creating')));
        exit;
    }

    public function edit($id)
    {
        Auth::requireSuperAdmin();
        $group = $this->group->getById($id);
        if (!$group) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=error&message=' . urlencode(Locale::get('group_not_found')));
            exit;
        }
        require __DIR__ . '/../views/groups/edit.php';
    }

    public function update($id)
    {
        Auth::requireSuperAdmin();
        $returnUrl = $this->getReturnUrl();

        $existingGroup = $this->group->getById($id);
        if (!$existingGroup) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=error&message=' . urlencode(Locale::get('group_not_found')));
            exit;
        }

        $this->group->id = $id;
        $this->group->name = $_POST['name'];
        $this->group->description = $_POST['description'] ?? null;
        $this->group->status = !empty($_POST['status']) ? (int)$_POST['status'] : 1;

        if ($this->group->isNameTaken($this->group->name, $id)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('group_name_taken')));
            exit;
        }

        // Build changes
        $changes = [];
        if ($existingGroup['name'] !== $this->group->name) {
            $changes['Name'] = ['old' => $existingGroup['name'], 'new' => $this->group->name];
        }
        if ($existingGroup['description'] !== $this->group->description) {
            $changes['Description'] = ['old' => $existingGroup['description'], 'new' => $this->group->description];
        }
        if ((int)$existingGroup['status'] !== $this->group->status) {
            $changes['Status'] = ['old' => $existingGroup['status'], 'new' => $this->group->status];
        }

        if ($this->group->update()) {
            ActivityLog::log('group_updated', null, null,
                ['group_id' => $id, 'name' => $this->group->name],
                !empty($changes) ? $changes : null);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function delete($id)
    {
        Auth::requireSuperAdmin();

        $group = $this->group->getById($id);
        if (!$group) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=error&message=' . urlencode(Locale::get('group_not_found')));
            exit;
        }

        // Check if group has users
        $userCount = $this->group->getUsersByGroupId($id);
        if ($userCount > 0) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=error&message=' . urlencode(Locale::get('group_has_users')));
            exit;
        }

        if ($this->group->delete($id)) {
            ActivityLog::log('group_deleted', null, null,
                ['group_id' => $id, 'name' => $group['name']]);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=success&message=' . urlencode(Locale::get('deleted_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=group&toast=error&message=' . urlencode(Locale::get('error_deleting')));
        exit;
    }

    public function getJson($id)
    {
        Auth::requireSuperAdmin();

        header('Content-Type: application/json');

        $group = $this->group->getById($id);
        if (!$group) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }

        echo json_encode($group);
        exit;
    }
}
