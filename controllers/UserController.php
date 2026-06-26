<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class UserController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    private function getReturnUrl()
    {
        return $_GET['return'] ?? $_POST['return'] ?? 'index.php?module=user';
    }

    public function index()
    {
        $users = $this->user->getAll();
        require __DIR__ . '/../views/users/list.php';
    }

    public function create()
    {
        require __DIR__ . '/../views/users/create.php';
    }

    public function store()
    {
        $returnUrl = $this->getReturnUrl();
        
        $this->user->firstname = $_POST['firstname'];
        $this->user->lastname = $_POST['lastname'];
        $this->user->username = $_POST['username'] ?? null;
        $this->user->telephone = $_POST['telephone'] ?? null;
        $this->user->comments = $_POST['comments'] ?? null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;
        $this->user->role = !empty($_POST['role']) ? (int)$_POST['role'] : 1;
        $this->user->password = 'password';

        if (!empty($this->user->username) && $this->user->isUsernameTaken($this->user->username)) {
            header('Location: index.php?module=user&action=create&toast=error&message=' . urlencode(Locale::get('username_taken')));
            exit;
        }

        $this->user->picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: index.php?module=user&action=create&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                exit;
            }
            $this->user->picture = $uploaded;
        }

        if ($this->user->create()) {
            ActivityLog::log('user_created', $this->user->id, $this->user->firstname . ' ' . $this->user->lastname, 
                ['username' => $this->user->username, 'role' => $this->user->role]);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        header('Location: index.php?module=user&action=create&toast=error&message=' . urlencode(Locale::get('error_creating')));
        exit;
    }

    public function edit($id)
    {
        $user = $this->user->getById($id);
        require __DIR__ . '/../views/users/edit.php';
    }

    public function update($id)
    {
        $returnUrl = $this->getReturnUrl();
        
        $this->user->id = $id;
        $this->user->firstname = $_POST['firstname'];
        $this->user->lastname = $_POST['lastname'];
        $this->user->username = $_POST['username'] ?? null;
        $this->user->telephone = $_POST['telephone'] ?? null;
        $this->user->comments = $_POST['comments'] ?? null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;
        $this->user->role = !empty($_POST['role']) ? (int)$_POST['role'] : 1;

        if (!empty($this->user->username) && $this->user->isUsernameTaken($this->user->username, $id)) {
            header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('username_taken')));
            exit;
        }

        $existingUser = $this->user->getById($id);
        $this->user->picture = $existingUser['picture'] ?? null;

        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            if ($this->user->picture) {
                $this->user->deletePicture($this->user->picture);
            }

            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                exit;
            }
            $this->user->picture = $uploaded;
        }

        if (isset($_POST['remove_picture']) && $_POST['remove_picture'] === '1') {
            if ($this->user->picture) {
                $this->user->deletePicture($this->user->picture);
                $this->user->picture = null;
            }
        }

        if ($this->user->update()) {
            ActivityLog::log('user_updated', $id, $this->user->firstname . ' ' . $this->user->lastname, 
                ['username' => $this->user->username, 'role' => $this->user->role]);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function resetPassword($id)
    {
        if ($this->user->resetPassword($id)) {
            ActivityLog::log('password_reset', $id, null, ['reset_by' => Auth::getUserId()]);
            header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=success&message=' . urlencode(Locale::get('password_reset_successfully')));
            exit;
        }
        header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_resetting_password')));
        exit;
    }

    public function delete($id)
    {
        $userData = $this->user->getById($id);
        if ($this->user->delete($id)) {
            ActivityLog::log('user_deleted', $id, $userData['firstname'] . ' ' . $userData['lastname'] ?? null);
            header('Location: index.php?module=user&toast=success&message=' . urlencode(Locale::get('deleted_successfully')));
            exit;
        }
        header('Location: index.php?module=user&toast=error&message=' . urlencode(Locale::get('error_deleting')));
        exit;
    }
}
