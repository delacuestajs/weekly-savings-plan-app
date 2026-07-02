<?php

require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../controllers/Auth.php';

class ExpenseController
{
    private $expense;

    public function __construct()
    {
        $this->expense = new Expense();
    }

    public function store()
    {
        Auth::requireAdmin();

        $this->expense->activity_id = $_POST['activity_id'];
        $this->expense->description = trim($_POST['description'] ?? '');
        $this->expense->amount = $_POST['amount'];
        $this->expense->status = 'pending';
        $this->expense->bag_id = Auth::getBagId();

        if ($this->expense->create()) {
            ActivityLog::log('expense_created', null, null, 
                ['activity_id' => $_POST['activity_id'], 'amount' => $_POST['amount'], 'description' => $_POST['description']]);
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('expense_created_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_creating_expense')));
        exit;
    }

    public function getJson($id)
    {
        Auth::requireAdmin();
        
        header('Content-Type: application/json');
        
        $expense = $this->expense->getById($id);
        if (!$expense) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }
        
        echo json_encode($expense);
        exit;
    }

    public function update($id)
    {
        Auth::requireAdmin();

        $expense = $this->expense->getById($id);
        if (!$expense) {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('expense_not_found')));
            exit;
        }

        if ($expense['status'] === 'confirmed') {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('cannot_edit_confirmed_expense')));
            exit;
        }

        $this->expense->id = $id;
        $this->expense->description = trim($_POST['description'] ?? '');
        $this->expense->amount = $_POST['amount'];

        if ($this->expense->update()) {
            ActivityLog::log('expense_updated', null, null, 
                ['expense_id' => $id, 'amount' => $_POST['amount']], 
                ['Description' => ['old' => $expense['description'], 'new' => $_POST['description']], 
                 'Amount' => ['old' => $expense['amount'], 'new' => $_POST['amount']]]);
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('expense_updated_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_updating_expense')));
        exit;
    }

    public function confirm($id)
    {
        Auth::requireAdmin();

        $expense = $this->expense->getById($id);
        if (!$expense) {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('expense_not_found')));
            exit;
        }

        $this->expense->id = $id;
        if ($this->expense->confirm()) {
            ActivityLog::log('expense_confirmed', null, null, 
                ['expense_id' => $id, 'amount' => $expense['amount'], 'description' => $expense['description']]);
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('expense_confirmed_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_confirming_expense')));
        exit;
    }

    public function delete($id)
    {
        Auth::requireAdmin();

        $expense = $this->expense->getById($id);
        if (!$expense) {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('expense_not_found')));
            exit;
        }

        if ($expense['status'] === 'confirmed') {
            header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('cannot_delete_confirmed_expense')));
            exit;
        }

        if ($this->expense->delete($id)) {
            ActivityLog::log('expense_deleted', null, null, 
                ['expense_id' => $id, 'amount' => $expense['amount'], 'description' => $expense['description']]);
            header('Location: index.php?module=activity&toast=success&message=' . urlencode(Locale::get('expense_deleted_successfully')));
            exit;
        }
        header('Location: index.php?module=activity&toast=error&message=' . urlencode(Locale::get('error_deleting_expense')));
        exit;
    }
}
