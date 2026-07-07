<?php

require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/WeeklySaving.php';
require_once __DIR__ . '/../controllers/Auth.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../helpers/mail.php';

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

    private function getReturnUrl()
    {
        return $_GET['return'] ?? $_POST['return'] ?? ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments';
    }

    private function getUserName($userId)
    {
        if (!$userId) return null;
        $user = $this->user->getById($userId);
        return $user ? $user['firstname'] . ' ' . $user['lastname'] : null;
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
        
        $bagId = Auth::getBagId();
        $savings = $this->saving->getAll($filters, $bagId);
        $total = $this->saving->getTotalSavings(!Auth::isAdmin() ? Auth::getUserId() : null, $bagId);
        $usersList = $this->user->getAll($bagId)->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/list.php';
    }

    public function weekly()
    {
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        
        if (!Auth::isAdmin()) {
            $userId = Auth::getUserId();
        }
        
        $bagId = Auth::getBagId();
        $data = $this->weeklySaving->getWeeklyOverview($year, $userId, $bagId);
        $usersList = $this->user->getAll($bagId)->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/weekly.php';
    }

    public function create()
    {
        $bagId = Auth::getBagId();
        $stmt = $this->user->getAll($bagId);
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
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=create&toast=error&message=' . urlencode(Locale::get('user_required')));
            exit;
        }
        
        $this->saving->user_id = $_POST['user_id'];
        $this->saving->description = trim($_POST['description'] ?? '');
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = 'unverified';
        $this->saving->notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        $this->saving->bag_id = Auth::getBagId();
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] . ' 00:00:00' : date('Y-m-d H:i:s');

        $this->saving->attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
                if ($uploaded === false) {
                    header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=create&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                    exit;
                }
                $this->saving->attachment = $uploaded;
            } elseif ($_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['attachment']['error'] === UPLOAD_ERR_FORM_SIZE) {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=create&toast=error&message=' . urlencode(Locale::get('file_too_large')));
                exit;
            } else {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=create&toast=error&message=' . urlencode(Locale::get('upload_error')));
                exit;
            }
        }

        if ($this->saving->create()) {
            $ownerName = $this->getUserName($this->saving->user_id);
            ActivityLog::log('saving_created', $this->saving->user_id, $ownerName, 
                ['amount' => $this->saving->amount, 'method' => $this->saving->payment_method, 'description' => $this->saving->description]);

            // Send email to admin users of this bag
            $this->sendPaymentCreatedEmail($this->saving->user_id, $this->saving->amount, $this->saving->payment_method, $this->saving->description);

            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=create&toast=error&message=' . urlencode(Locale::get('error_creating')));
        exit;
    }

    public function edit($id)
    {
        $saving = $this->saving->getById($id);
        
        if ($saving['status'] === 'verified') {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=error&message=' . urlencode(Locale::get('cannot_edit_verified')));
            exit;
        }
        
        $bagId = Auth::getBagId();
        $stmt = $this->user->getAll($bagId);
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
        
        // Get existing values before update
        $existingSaving = $this->saving->getById($id);
        
        $this->saving->id = $id;
        $this->saving->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
        $this->saving->description = trim($_POST['description'] ?? '');
        $this->saving->amount = $_POST['amount'];
        $this->saving->payment_method = $_POST['payment_method'];
        $this->saving->status = $_POST['status'];
        $this->saving->notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        $this->saving->created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');

        $this->saving->attachment = $existingSaving['attachment'] ?? null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                if ($this->saving->attachment) {
                    $this->saving->deleteAttachment($this->saving->attachment);
                }

                $uploaded = $this->saving->uploadAttachment($_FILES['attachment']);
                if ($uploaded === false) {
                    header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                    exit;
                }
                $this->saving->attachment = $uploaded;
            } elseif ($_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['attachment']['error'] === UPLOAD_ERR_FORM_SIZE) {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('file_too_large')));
                exit;
            } else {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('upload_error')));
                exit;
            }
        }

        if (isset($_POST['remove_attachment']) && $_POST['remove_attachment'] === '1') {
            if ($this->saving->attachment) {
                $this->saving->deleteAttachment($this->saving->attachment);
                $this->saving->attachment = null;
            }
        }

        // Build changes object comparing old vs new
        $changes = [];
        $fieldsToTrack = [
            'description' => 'Description',
            'amount' => 'Amount',
            'payment_method' => 'Payment Method',
            'status' => 'Status',
            'notes' => 'Notes',
            'created_at' => 'Date'
        ];
        
        foreach ($fieldsToTrack as $field => $label) {
            $oldValue = $existingSaving[$field] ?? null;
            $newValue = $this->saving->$field ?? null;
            
            // Normalize for comparison
            if ($field === 'created_at') {
                $oldValue = $oldValue ? date('Y-m-d', strtotime($oldValue)) : null;
                $newValue = $newValue ? date('Y-m-d', strtotime($newValue)) : null;
            }
            
            if ($oldValue != $newValue) {
                $changes[$label] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        if ($this->saving->update()) {
            $ownerName = $this->getUserName($this->saving->user_id);
            ActivityLog::log('saving_updated', $this->saving->user_id, $ownerName, 
                ['saving_id' => $id], 
                !empty($changes) ? $changes : null);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function verify($id)
    {
        $saving = $this->saving->getById($id);
        if (!$saving) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=error&message=' . urlencode(Locale::get('error_updating')));
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
            $ownerName = $this->getUserName($saving['user_id']);
            ActivityLog::log('saving_verified', $saving['user_id'], $ownerName, 
                ['saving_id' => $id, 'amount' => $saving['amount'], 'description' => $saving['description']]);

            // Send email to payment owner
            $this->sendPaymentVerifiedEmail($saving['user_id'], $saving['amount'], $saving['payment_method'], $saving['description']);

            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=success&message=' . urlencode(Locale::get('payment_verified')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function delete($id)
    {
        $saving = $this->saving->getById($id);
        if ($this->saving->delete($id)) {
            $ownerName = $this->getUserName($saving['user_id'] ?? null);
            ActivityLog::log('saving_deleted', $saving['user_id'] ?? null, $ownerName, 
                ['saving_id' => $id, 'amount' => $saving['amount'] ?? null]);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=success&message=' . urlencode(Locale::get('deleted_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?action=payments&toast=error&message=' . urlencode(Locale::get('error_deleting')));
        exit;
    }

    public function getJson($id)
    {
        Auth::requireLogin();
        
        header('Content-Type: application/json');
        
        $saving = $this->saving->getById($id);
        if (!$saving) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }
        
        // Format date for input
        if (!empty($saving['created_at'])) {
            $dateObj = new DateTime($saving['created_at']);
            $saving['created_at_formatted'] = $dateObj->format('Y-m-d');
        } else {
            $saving['created_at_formatted'] = date('Y-m-d');
        }
        
        echo json_encode($saving);
        exit;
    }

    private function sendPaymentCreatedEmail($userId, $amount, $paymentMethod, $description)
    {
        $user = $this->user->getById($userId);
        if (!$user) return;

        $admins = $this->user->getAdminsByBag($user['bag_id']);
        if (empty($admins)) return;

        $lang = Locale::getCurrentLanguage();
        $payerName = $user['firstname'] . ' ' . $user['lastname'];
        $subject = $lang === 'es' ? "Nuevo pago registrado" : "New payment recorded";
        $methodLabel = $lang === 'es' ? 'Método de pago' : 'Payment method';
        $methodValue = $lang === 'es' ? ($paymentMethod == 2 ? 'Pago Fijo' : 'Número de Semana') : ($paymentMethod == 2 ? 'Fixed Payment' : 'Week Number');

        foreach ($admins as $admin) {
            if (empty($admin['email'])) continue;

            $adminName = $admin['firstname'];

            if ($lang === 'es') {
                $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                    <h2 style='color:#2563eb;'>Hola {$adminName}</h2>
                    <p>Se ha registrado un nuevo pago en <strong>Savings App</strong>.</p>
                    <p><strong>Usuario:</strong> {$payerName}</p>
                    <p><strong>Monto:</strong> $" . number_format($amount, 0, ',', '.') . "</p>
                    <p><strong>{$methodLabel}:</strong> {$methodValue}</p>";
                if ($description) {
                    $body .= "<p><strong>Descripción:</strong> " . htmlspecialchars($description) . "</p>";
                }
                $body .= "<p><strong>Estado:</strong> Pendiente de verificación</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                    <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
                </div>";
            } else {
                $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                    <h2 style='color:#2563eb;'>Hello {$adminName}</h2>
                    <p>A new payment has been recorded in <strong>Savings App</strong>.</p>
                    <p><strong>User:</strong> {$payerName}</p>
                    <p><strong>Amount:</strong> $" . number_format($amount, 0, ',', '.') . "</p>
                    <p><strong>{$methodLabel}:</strong> {$methodValue}</p>";
                if ($description) {
                    $body .= "<p><strong>Description:</strong> " . htmlspecialchars($description) . "</p>";
                }
                $body .= "<p><strong>Status:</strong> Pending verification</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                    <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
                </div>";
            }

            Mail::sendAsync($admin['email'], $subject, $body);
        }
    }

    private function sendPaymentVerifiedEmail($userId, $amount, $paymentMethod, $description)
    {
        $user = $this->user->getById($userId);
        if (!$user || empty($user['email'])) return;

        $lang = Locale::getCurrentLanguage();
        $name = $user['firstname'];
        $subject = $lang === 'es' ? "Pago verificado" : "Payment verified";
        $methodLabel = $lang === 'es' ? 'Método de pago' : 'Payment method';
        $methodValue = $lang === 'es' ? ($paymentMethod == 2 ? 'Pago Fijo' : 'Número de Semana') : ($paymentMethod == 2 ? 'Fixed Payment' : 'Week Number');

        if ($lang === 'es') {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hola {$name}</h2>
                <p>Tu pago en <strong>Savings App</strong> ha sido verificado.</p>
                <p><strong>Monto:</strong> $" . number_format($amount, 0, ',', '.') . "</p>
                <p><strong>{$methodLabel}:</strong> {$methodValue}</p>";
            if ($description) {
                $body .= "<p><strong>Descripción:</strong> " . htmlspecialchars($description) . "</p>";
            }
            $body .= "<p><strong>Estado:</strong> Verificado ✓</p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        } else {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hello {$name}</h2>
                <p>Your payment in <strong>Savings App</strong> has been verified.</p>
                <p><strong>Amount:</strong> $" . number_format($amount, 0, ',', '.') . "</p>
                <p><strong>{$methodLabel}:</strong> {$methodValue}</p>";
            if ($description) {
                $body .= "<p><strong>Description:</strong> " . htmlspecialchars($description) . "</p>";
            }
            $body .= "<p><strong>Status:</strong> Verified ✓</p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        }

        Mail::sendAsync($user['email'], $subject, $body);
    }
}
