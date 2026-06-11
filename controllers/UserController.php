<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
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
        $this->user->firstname = $_POST['firstname'];
        $this->user->lastname = $_POST['lastname'];
        $this->user->nickname = $_POST['nickname'] ?? null;
        $this->user->telephone = $_POST['telephone'] ?? null;
        $this->user->comments = $_POST['comments'] ?? null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;

        $this->user->picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: index.php?module=user&action=create&toast=error&message=' . urlencode('Invalid picture type or file too large. Allowed: JPG, PNG, GIF, WebP (max 5MB)'));
                exit;
            }
            $this->user->picture = $uploaded;
        }

        if ($this->user->create()) {
            header('Location: index.php?module=user&toast=success&message=' . urlencode('User created successfully'));
            exit;
        }
        header('Location: index.php?module=user&toast=error&message=' . urlencode('Error creating user'));
        exit;
    }

    public function edit($id)
    {
        $user = $this->user->getById($id);
        require __DIR__ . '/../views/users/edit.php';
    }

    public function update($id)
    {
        $this->user->id = $id;
        $this->user->firstname = $_POST['firstname'];
        $this->user->lastname = $_POST['lastname'];
        $this->user->nickname = $_POST['nickname'] ?? null;
        $this->user->telephone = $_POST['telephone'] ?? null;
        $this->user->comments = $_POST['comments'] ?? null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;

        $existingUser = $this->user->getById($id);
        $this->user->picture = $existingUser['picture'] ?? null;

        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            if ($this->user->picture) {
                $this->user->deletePicture($this->user->picture);
            }

            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: index.php?module=user&action=edit&id=' . $id . '&toast=error&message=' . urlencode('Invalid picture type or file too large. Allowed: JPG, PNG, GIF, WebP (max 5MB)'));
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
            header('Location: index.php?module=user&toast=success&message=' . urlencode('User updated successfully'));
            exit;
        }
        header('Location: index.php?module=user&toast=error&message=' . urlencode('Error updating user'));
        exit;
    }

    public function delete($id)
    {
        if ($this->user->delete($id)) {
            header('Location: index.php?module=user&toast=success&message=' . urlencode('User deleted successfully'));
            exit;
        }
        header('Location: index.php?module=user&toast=error&message=' . urlencode('Error deleting user'));
        exit;
    }
}
