<?php

require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/WeeklySaving.php';

class SavingController
{
    private $saving;
    private $user;
    private $weeklySaving;

    public function __construct()
    {
        $this->saving = new Saving();
        $this->user = new User();
        $this->weeklySaving = new WeeklySaving();
    }

    public function index()
    {
        $savings = $this->saving->getAll();
        $total = $this->saving->getTotalSavings();
        require __DIR__ . '/../views/list.php';
    }

    public function weekly()
    {
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        $data = $this->weeklySaving->getWeeklyOverview($year, $userId);
        $usersList = $this->user->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/weekly.php';
    }

    public function create()
    {
        $stmt = $this->user->getAll();
        $usersArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $usersData = [];
        foreach ($usersArray as $row) {
            $usersData[$row['id']] = [
                'id' => $row['id'],
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'picture' => $row['picture']
            ];
        }
        $users = $usersArray;
        require __DIR__ . '/../views/create.php';
    }

    public function store()
    {
        $this->saving->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
        $this->saving->name = $_POST['name'];
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = $_POST['status'];
        $this->saving->description = $_POST['description'];
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');

        $this->saving->attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
            if ($uploaded === false) {
                header('Location: index.php?action=create&toast=error&message=' . urlencode('Invalid file type or file too large. Allowed: JPG, PNG, GIF, WebP, PDF, DOC, DOCX (max 5MB)'));
                exit;
            }
            $this->saving->attachment = $uploaded;
        }

        if ($this->saving->create()) {
            header('Location: index.php?toast=success&message=' . urlencode('Saving created successfully'));
            exit;
        }
        header('Location: index.php?toast=error&message=' . urlencode('Error creating saving'));
        exit;
    }

    public function edit($id)
    {
        $saving = $this->saving->getById($id);
        $stmt = $this->user->getAll();
        $usersArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $usersData = [];
        foreach ($usersArray as $row) {
            $usersData[$row['id']] = [
                'id' => $row['id'],
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'picture' => $row['picture']
            ];
        }
        $users = $usersArray;
        require __DIR__ . '/../views/edit.php';
    }

    public function update($id)
    {
        $this->saving->id = $id;
        $this->saving->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
        $this->saving->name = $_POST['name'];
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = $_POST['status'];
        $this->saving->description = $_POST['description'];
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');

        $existingSaving = $this->saving->getById($id);
        $this->saving->attachment = $existingSaving['attachment'] ?? null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            if ($this->saving->attachment) {
                $this->saving->deleteAttachment($this->saving->attachment);
            }

            $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
            if ($uploaded === false) {
                header('Location: index.php?action=edit&id=' . $id . '&toast=error&message=' . urlencode('Invalid file type or file too large. Allowed: JPG, PNG, GIF, WebP, PDF, DOC, DOCX (max 5MB)'));
                exit;
            }
            $this->saving->attachment = $uploaded;
        }

        if (isset($_POST['remove_attachment']) && $_POST['remove_attachment'] === '1') {
            if ($this->saving->attachment) {
                $this->saving->deleteAttachment($this->saving->attachment);
                $this->saving->attachment = null;
            }
        }

        if ($this->saving->update()) {
            header('Location: index.php?toast=success&message=' . urlencode('Saving updated successfully'));
            exit;
        }
        header('Location: index.php?toast=error&message=' . urlencode('Error updating saving'));
        exit;
    }

    public function delete($id)
    {
        if ($this->saving->delete($id)) {
            header('Location: index.php?toast=success&message=' . urlencode('Saving deleted successfully'));
            exit;
        }
        header('Location: index.php?toast=error&message=' . urlencode('Error deleting saving'));
        exit;
    }
}
