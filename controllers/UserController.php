<?php

require_once __DIR__ . '/../config/database.php';
$appConfig = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../helpers/mail.php';

class UserController
{
    private $conn;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->user = new User();
    }

    private function getReturnUrl()
    {
        return $_GET['return'] ?? $_POST['return'] ?? ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user';
    }

    public function index()
    {
        $bagId = Auth::getBagId();
        $includeSuperAdmin = Auth::isSuperAdmin();
        $users = $this->user->getAll($bagId, $includeSuperAdmin);
        $usersWithVerifiedPayments = $this->getUsersWithVerifiedPayments();
        require __DIR__ . '/../views/users/list.php';
    }

    private function getUsersWithVerifiedPayments()
    {
        $query = "SELECT DISTINCT s.user_id FROM savings s 
                  INNER JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.status = 'verified' AND s.is_active = 1 AND s.deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }

    public function store()
    {
        global $appConfig;
        $returnUrl = $this->getReturnUrl();
        
        $this->user->firstname = trim($_POST['firstname'] ?? '');
        $this->user->lastname = trim($_POST['lastname'] ?? '');
        $this->user->username = !empty($_POST['username']) ? trim($_POST['username']) : null;
        $this->user->telephone = !empty($_POST['telephone']) ? trim($_POST['telephone']) : null;
        $this->user->email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $this->user->comments = !empty($_POST['comments']) ? trim($_POST['comments']) : null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;
        $this->user->payment_system = !empty($_POST['payment_system']) ? (int)$_POST['payment_system'] : 1;
        $this->user->role = !empty($_POST['role']) ? (int)$_POST['role'] : 1;
        $this->user->bag_id = !empty($_POST['bag_id']) ? (int)$_POST['bag_id'] : Auth::getBagId();
        $this->user->password = User::getDefaultPassword();

        // Admin cannot create superadmin users
        if ($this->user->role == 3 && !Auth::isSuperAdmin()) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }

        // Email is required
        if (empty($this->user->email)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('email_required')));
            exit;
        }

        if (!filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('email_invalid')));
            exit;
        }

        if (!empty($this->user->username) && $this->user->isUsernameTaken($this->user->username, null, $this->user->bag_id)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('username_taken')));
            exit;
        }

        $this->user->picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                exit;
            }
            $this->user->picture = $uploaded;
        }

        if ($this->user->create()) {
            ActivityLog::log('user_created', $this->user->id, $this->user->firstname . ' ' . $this->user->lastname, 
                ['username' => $this->user->username, 'role' => $this->user->role, 'bag_id' => $this->user->bag_id]);
            
            // Get bag name for credentials modal
            $bagModel = new Bag();
            $bag = $bagModel->getById($this->user->bag_id);
            
            $_SESSION['new_user_credentials'] = [
                'username' => $this->user->username,
                'password' => User::getDefaultPassword(),
                'name' => $this->user->firstname . ' ' . $this->user->lastname,
                'group' => $bag ? ($bag['long_name'] ?? $bag['name']) : null
            ];

            // Send email to new user
            if (!empty($this->user->email)) {
                $this->sendUserCreatedEmail(
                    $this->user->email,
                    $this->user->firstname,
                    $this->user->username,
                    User::getDefaultPassword(),
                    $bag ? ($bag['long_name'] ?? $bag['name']) : '',
                    $appConfig['base_url']
                );
            }

            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        
        ActivityLog::log('user_creation_failed', null, $this->user->firstname . ' ' . $this->user->lastname,
            ['username' => $this->user->username, 'bag_id' => $this->user->bag_id, 'reason' => 'duplicate_or_error']);
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('username_taken')));
        exit;
    }

    public function update($id)
    {
        $returnUrl = $this->getReturnUrl();
        
        $this->user->id = $id;
        $this->user->firstname = trim($_POST['firstname'] ?? '');
        $this->user->lastname = trim($_POST['lastname'] ?? '');
        $this->user->username = !empty($_POST['username']) ? trim($_POST['username']) : null;
        $this->user->telephone = !empty($_POST['telephone']) ? trim($_POST['telephone']) : null;
        $this->user->email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $this->user->comments = !empty($_POST['comments']) ? trim($_POST['comments']) : null;
        $this->user->multiplier = !empty($_POST['multiplier']) ? (int)$_POST['multiplier'] : 1;
        $this->user->payment_system = !empty($_POST['payment_system']) ? (int)$_POST['payment_system'] : 1;
        $this->user->role = !empty($_POST['role']) ? (int)$_POST['role'] : 1;
        $this->user->bag_id = !empty($_POST['bag_id']) ? (int)$_POST['bag_id'] : null;

        $existingUser = $this->user->getById($id);

        // If bag_id not in form (non-superadmin), keep existing bag
        if ($this->user->bag_id === null) {
            $this->user->bag_id = $existingUser['bag_id'] ?? null;
        }

        // Admin cannot promote to superadmin
        if ($this->user->role == 3 && !Auth::isSuperAdmin()) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }

        // Admin cannot change superadmin role
        if (($existingUser['role'] ?? 0) == 3 && !Auth::isSuperAdmin()) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }

        // Email is required
        if (empty($this->user->email)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('email_required')));
            exit;
        }

        if (!filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('email_invalid')));
            exit;
        }

        // Check if bag is changing
        $oldBagId = $existingUser['bag_id'] ?? null;
        $newBagId = $this->user->bag_id;
        $bagChanged = ($oldBagId != $newBagId);

        // Only superadmin can change bag
        if ($bagChanged && !Auth::isSuperAdmin()) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('admin_required')));
            exit;
        }

        // If bag is changing, check for verified payments
        if ($bagChanged && $this->hasVerifiedPayments($id)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('cannot_move_user_verified_payments')));
            exit;
        }

        // Check username uniqueness in destination bag
        if (!empty($this->user->username) && $this->user->isUsernameTaken($this->user->username, $id, $newBagId)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('username_taken')));
            exit;
        }

        $this->user->picture = $existingUser['picture'] ?? null;

        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            if ($this->user->picture) {
                $this->user->deletePicture($this->user->picture);
            }
            $uploaded = $this->user->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('invalid_file')));
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
            // If bag changed, update related records and log the movement
            if ($bagChanged) {
                $this->moveUserToBag($id, $oldBagId, $newBagId);
            }

            ActivityLog::log('user_updated', $id, $this->user->firstname . ' ' . $this->user->lastname, 
                ['username' => $this->user->username, 'role' => $this->user->role, 'bag_id' => $this->user->bag_id]);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    private function moveUserToBag($userId, $oldBagId, $newBagId)
    {
        // Update savings bag_id
        $query = "UPDATE savings SET bag_id = :new_bag_id WHERE user_id = :user_id AND bag_id = :old_bag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':new_bag_id', $newBagId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':old_bag_id', $oldBagId);
        $stmt->execute();

        // Update activity_logs bag_id
        $query = "UPDATE activity_logs SET bag_id = :new_bag_id WHERE user_id = :user_id AND bag_id = :old_bag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':new_bag_id', $newBagId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':old_bag_id', $oldBagId);
        $stmt->execute();

        // Get bag names for logging
        $bagModel = new Bag();
        $oldBag = $bagModel->getById($oldBagId);
        $newBag = $bagModel->getById($newBagId);

        // Log the movement
        ActivityLog::log('user_moved_to_bag', $userId, $this->user->firstname . ' ' . $this->user->lastname, [
            'old_bag_id' => $oldBagId,
            'old_bag_name' => $oldBag['name'] ?? 'Unknown',
            'new_bag_id' => $newBagId,
            'new_bag_name' => $newBag['name'] ?? 'Unknown',
            'moved_by' => Auth::getUserId()
        ]);
    }

    public function resetPassword($id)
    {
        global $appConfig;
        $userData = $this->user->getById($id);
        if ($this->user->resetPassword($id)) {
            ActivityLog::log('password_reset', $id, $userData['firstname'] . ' ' . $userData['lastname'], ['reset_by' => Auth::getUserId()]);
            
            // Get bag name for credentials modal
            $bagModel = new Bag();
            $bag = $bagModel->getById($userData['bag_id'] ?? null);
            
            $_SESSION['new_user_credentials'] = [
                'username' => $userData['username'],
                'password' => User::getDefaultPassword(),
                'name' => $userData['firstname'] . ' ' . $userData['lastname'],
                'group' => $bag ? ($bag['long_name'] ?? $bag['name']) : null,
                'reset' => true
            ];

            // Send email about password reset
            if (!empty($userData['email'])) {
                $this->sendPasswordResetEmail(
                    $userData['email'],
                    $userData['firstname'],
                    $userData['username'],
                    User::getDefaultPassword(),
                    $bag ? ($bag['long_name'] ?? $bag['name']) : '',
                    $appConfig['base_url']
                );
            }

            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=success&message=' . urlencode(Locale::get('password_reset_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('error_resetting_password')));
        exit;
    }

    public function delete($id)
    {
        $userData = $this->user->getById($id);
        if (!$userData) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('user_not_found')));
            exit;
        }
        
        // Check if this is the last superadmin
        if (($userData['role'] ?? 0) == 3) {
            $query = "SELECT COUNT(*) as count FROM users WHERE role = 3 AND status = 1 AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] <= 1) {
                header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('cannot_delete_last_superadmin')));
                exit;
            }
        }
        
        if ($this->hasVerifiedPayments($id)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('cannot_delete_user_verified_payments')));
            exit;
        }
        
        if ($this->user->delete($id)) {
            ActivityLog::log('user_deleted', $id, $userData['firstname'] . ' ' . $userData['lastname'] ?? null);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=success&message=' . urlencode(Locale::get('deleted_successfully')));
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?module=user&toast=error&message=' . urlencode(Locale::get('error_deleting')));
        exit;
    }

    private function hasVerifiedPayments($userId)
    {
        $query = "SELECT COUNT(*) as count FROM savings WHERE user_id = :user_id AND status = 'verified' AND is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getJson($id)
    {
        if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            exit;
        }

        header('Content-Type: application/json');

        $user = $this->user->getById($id);
        if (!$user) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }

        echo json_encode($user);
        exit;
    }

    public function updateProfile()
    {
        $userId = Auth::getUserId();
        $user = $this->user->getById($userId);

        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Email is required
        if (empty($email)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?toast=error&message=' . urlencode(Locale::get('email_required')));
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?toast=error&message=' . urlencode(Locale::get('email_invalid')));
            exit;
        }

        $changes = [];
        if ($firstname !== $user['firstname']) {
            $changes['Firstname'] = ['old' => $user['firstname'], 'new' => $firstname];
        }
        if ($lastname !== $user['lastname']) {
            $changes['Lastname'] = ['old' => $user['lastname'], 'new' => $lastname];
        }
        if ($telephone !== ($user['telephone'] ?? '')) {
            $changes['Telephone'] = ['old' => $user['telephone'] ?? '', 'new' => $telephone];
        }
        if ($email !== ($user['email'] ?? '')) {
            $changes['Email'] = ['old' => $user['email'] ?? '', 'new' => $email];
        }

        $this->user->id = $userId;
        $this->user->firstname = $firstname;
        $this->user->lastname = $lastname;
        $this->user->username = $user['username'];
        $this->user->telephone = $telephone;
        $this->user->email = $email;
        $this->user->comments = $user['comments'];
        $this->user->multiplier = $user['multiplier'];
        $this->user->payment_system = $user['payment_system'] ?? 1;
        $this->user->role = $user['role'];
        $this->user->bag_id = $user['bag_id'];
        $this->user->picture = $user['picture'];

        // Handle picture upload
        $pictureResult = null;
        $pictureError = null;
        
        if (!isset($_FILES['picture'])) {
            $pictureError = 'no_file_input';
        } elseif ($_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $pictureError = 'no_file_selected';
        } elseif ($_FILES['picture']['error'] !== UPLOAD_ERR_OK) {
            $pictureError = 'upload_error_' . $_FILES['picture']['error'];
        } else {
            $file = $_FILES['picture'];
            
            if ($this->user->picture) {
                $this->user->deletePicture($this->user->picture);
            }
            
            $uploaded = $this->user->uploadPicture($file);
            if ($uploaded === false) {
                $pictureError = 'validation_failed';
            } else {
                $this->user->picture = $uploaded;
                $pictureResult = $uploaded;
            }
        }

        if ($this->user->update()) {
            $_SESSION['user_name'] = $firstname . ' ' . $lastname;
            
            $logPayload = [
                'picture_status' => $pictureResult ? 'uploaded' : ($pictureError ?: 'none'),
            ];
            if ($pictureResult) {
                $logPayload['picture'] = $pictureResult;
                $logPayload['thumbnail'] = preg_replace('/\.[^.]+$/', '_thumb.jpg', $pictureResult);
            }
            
            if ($pictureResult) {
                $changes['Picture'] = ['old' => $user['picture'] ?? 'none', 'new' => $pictureResult];
            } elseif ($pictureError && $pictureError !== 'no_file_selected' && $pictureError !== 'no_file_input') {
                $changes['Picture'] = ['old' => $user['picture'] ?? 'none', 'new' => 'FAILED: ' . $pictureError];
            }
            
            ActivityLog::log('profile_updated', $userId, $firstname . ' ' . $lastname, $logPayload, !empty($changes) ? $changes : null);
            header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?toast=success&message=' . urlencode(Locale::get('profile_updated_successfully')));
            exit;
        }
        
        ActivityLog::log('profile_update_failed', $userId, $firstname . ' ' . $lastname, 
            ['reason' => 'database_error', 'picture_status' => $pictureResult ? 'uploaded' : $pictureError]);
        header('Location: ' . ($_SERVER['HTTP_X_BASE_PATH'] ?? '') . '/?toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    private function sendUserCreatedEmail($to, $firstname, $username, $password, $groupName, $baseUrl = '')
    {
        $lang = Locale::getCurrentLanguage();
        $subject = $lang === 'es' ? "Tu cuenta ha sido creada" : "Your account has been created";
        $name = $firstname;
        $loginUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');

        if ($lang === 'es') {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hola {$name}</h2>
                <p>Tu cuenta en <strong>Savings App</strong> ha sido creada exitosamente.</p>
                <p><strong>Grupo:</strong> {$groupName}</p>
                <p><strong>Usuario:</strong> {$username}</p>
                <p><strong>Contraseña:</strong> {$password}</p>
                <p style='color:#dc2626;font-size:13px;'>Por favor cambia tu contraseña después de iniciar sesión.</p>
                <p style='text-align:center;margin:24px 0;'><a href='{$loginUrl}' style='display:inline-block;background-color:#2563eb;color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:bold;'>Iniciar Sesión</a></p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        } else {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hello {$name}</h2>
                <p>Your account in <strong>Savings App</strong> has been created successfully.</p>
                <p><strong>Group:</strong> {$groupName}</p>
                <p><strong>Username:</strong> {$username}</p>
                <p><strong>Password:</strong> {$password}</p>
                <p style='color:#dc2626;font-size:13px;'>Please change your password after your first login.</p>
                <p style='text-align:center;margin:24px 0;'><a href='{$loginUrl}' style='display:inline-block;background-color:#2563eb;color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:bold;'>Login</a></p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        }

        Mail::sendAsync($to, $subject, $body);
    }

    private function sendPasswordResetEmail($to, $firstname, $username, $password, $groupName, $baseUrl = '')
    {
        $lang = Locale::getCurrentLanguage();
        $subject = $lang === 'es' ? "Contraseña restablecida" : "Password reset";
        $name = $firstname;
        $loginUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');

        if ($lang === 'es') {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hola {$name}</h2>
                <p>Tu contraseña en <strong>Savings App</strong> ha sido restablecida.</p>
                <p><strong>Grupo:</strong> {$groupName}</p>
                <p><strong>Usuario:</strong> {$username}</p>
                <p><strong>Nueva contraseña:</strong> {$password}</p>
                <p style='color:#dc2626;font-size:13px;'>Por favor cambia tu contraseña después de iniciar sesión.</p>
                <p style='text-align:center;margin:24px 0;'><a href='{$loginUrl}' style='display:inline-block;background-color:#2563eb;color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:bold;'>Iniciar Sesión</a></p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        } else {
            $body = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Hello {$name}</h2>
                <p>Your password in <strong>Savings App</strong> has been reset.</p>
                <p><strong>Group:</strong> {$groupName}</p>
                <p><strong>Username:</strong> {$username}</p>
                <p><strong>New password:</strong> {$password}</p>
                <p style='color:#dc2626;font-size:13px;'>Please change your password after logging in.</p>
                <p style='text-align:center;margin:24px 0;'><a href='{$loginUrl}' style='display:inline-block;background-color:#2563eb;color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:bold;'>Login</a></p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#9ca3af;'>Savings App</p>
            </div>";
        }

        Mail::sendAsync($to, $subject, $body);
    }
}
