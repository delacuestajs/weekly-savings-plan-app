<?php

require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/WeeklySaving.php';
require_once __DIR__ . '/../controllers/Auth.php';

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

    private function getReturnUrl($default = 'index.php?action=payments')
    {
        return $_GET['return'] ?? $_POST['return'] ?? $default;
    }

    public function index()
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'month' => $_GET['month'] ?? ''
        ];
        
        if (!Auth::isAdmin()) {
            $filters['user_id'] = Auth::getUserId();
        }
        
        $savings = $this->saving->getAll($filters);
        $total = $this->saving->getTotalSavings(!Auth::isAdmin() ? Auth::getUserId() : null);
        $usersList = $this->user->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/list.php';
    }

    public function weekly()
    {
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        
        if (!Auth::isAdmin()) {
            $userId = Auth::getUserId();
        }
        
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
        $returnUrl = $this->getReturnUrl();
        
        if (empty($_POST['user_id'])) {
            header('Location: index.php?action=create&toast=error&message=' . urlencode(Locale::get('user_required')));
            exit;
        }
        
        $this->saving->user_id = $_POST['user_id'];
        $this->saving->description = $_POST['description'];
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = 'unverified';
        $this->saving->notes = $_POST['notes'];
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] . ' 00:00:00' : date('Y-m-d H:i:s');

        $this->saving->attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
                if ($uploaded === false) {
                    header('Location: index.php?action=create&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                    exit;
                }
                $this->saving->attachment = $uploaded;
            } elseif ($_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['attachment']['error'] === UPLOAD_ERR_FORM_SIZE) {
                header('Location: index.php?action=create&toast=error&message=' . urlencode(Locale::get('file_too_large')));
                exit;
            } else {
                header('Location: index.php?action=create&toast=error&message=' . urlencode(Locale::get('upload_error')));
                exit;
            }
        }

        if ($this->saving->create()) {
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        header('Location: index.php?action=create&toast=error&message=' . urlencode(Locale::get('error_creating')));
        exit;
    }

    public function edit($id)
    {
        $saving = $this->saving->getById($id);
        
        if ($saving['status'] === 'verified') {
            header('Location: index.php?action=payments&toast=error&message=' . urlencode(Locale::get('cannot_edit_verified')));
            exit;
        }
        
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
        $returnUrl = $this->getReturnUrl();
        
        $this->saving->id = $id;
        $this->saving->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
        $this->saving->description = $_POST['description'];
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = $_POST['status'];
        $this->saving->notes = $_POST['notes'];
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');

        $existingSaving = $this->saving->getById($id);
        $this->saving->attachment = $existingSaving['attachment'] ?? null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                if ($this->saving->attachment) {
                    $this->saving->deleteAttachment($this->saving->attachment);
                }

                $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
                if ($uploaded === false) {
                    header('Location: index.php?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                    exit;
                }
                $this->saving->attachment = $uploaded;
            } elseif ($_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['attachment']['error'] === UPLOAD_ERR_FORM_SIZE) {
                header('Location: index.php?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('file_too_large')));
                exit;
            } else {
                header('Location: index.php?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('upload_error')));
                exit;
            }
        }

        if (isset($_POST['remove_attachment']) && $_POST['remove_attachment'] === '1') {
            if ($this->saving->attachment) {
                $this->saving->deleteAttachment($this->saving->attachment);
                $this->saving->attachment = null;
            }
        }

        if ($this->saving->update()) {
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: index.php?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function verify($id)
    {
        $saving = $this->saving->getById($id);
        if (!$saving) {
            header('Location: index.php?action=payments&toast=error&message=' . urlencode(Locale::get('error_updating')));
            exit;
        }
        
        $this->saving->id = $id;
        $this->saving->user_id = $saving['user_id'];
        $this->saving->description = $saving['description'];
        $this->saving->amount = $saving['amount'];
        $this->saving->payment_method = $saving['payment_method'];
        $this->saving->status = 'verified';
        $this->saving->notes = $saving['notes'];
        $this->saving->attachment = $saving['attachment'];
        $this->saving->created_at = $saving['created_at'];
        
        if ($this->saving->update()) {
            header('Location: index.php?action=payments&toast=success&message=' . urlencode(Locale::get('payment_verified')));
            exit;
        }
        header('Location: index.php?action=payments&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function delete($id)
    {
        if ($this->saving->delete($id)) {
            header('Location: index.php?action=payments&toast=success&message=' . urlencode(Locale::get('deleted_successfully')));
            exit;
        }
        header('Location: index.php?action=payments&toast=error&message=' . urlencode(Locale::get('error_deleting')));
        exit;
    }
}
