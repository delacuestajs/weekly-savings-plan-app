<?php

require_once __DIR__ . '/../models/Activity.php';

class ActivityController
{
    private $activity;

    public function __construct()
    {
        $this->activity = new Activity();
    }

    public function index()
    {
        $activities = $this->activity->getAll();
        require __DIR__ . '/../views/activities/list.php';
    }

    public function create()
    {
        require __DIR__ . '/../views/activities/create.php';
    }

    public function store()
    {
        $this->activity->name = $_POST['name'];
        $this->activity->description = $_POST['description'];
        $this->activity->value = $_POST['value'];
        $this->activity->activity_date = $_POST['activity_date'];
        $this->activity->created_at = date('Y-m-d H:i:s');

        if ($this->activity->create()) {
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('activity_created_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_creating_activity')));
        exit;
    }

    public function edit($id)
    {
        $activity = $this->activity->getById($id);
        if (!$activity) {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('activity_not_found')));
            exit;
        }
        require __DIR__ . '/../views/activities/edit.php';
    }

    public function update($id)
    {
        $this->activity->id = $id;
        $this->activity->name = $_POST['name'];
        $this->activity->description = $_POST['description'];
        $this->activity->value = $_POST['value'];
        $this->activity->activity_date = $_POST['activity_date'];

        if ($this->activity->update()) {
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('activity_updated_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_updating_activity')));
        exit;
    }

    public function delete($id)
    {
        if ($this->activity->delete($id)) {
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('activity_deleted_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_deleting_activity')));
        exit;
    }
}
