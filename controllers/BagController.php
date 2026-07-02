<?php

require_once __DIR__ . '/../models/Bag.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class BagController
{
    private $bag;

    public function __construct()
    {
        $this->bag = new Bag();
    }

    private function getReturnUrl()
    {
        return $_GET['return'] ?? $_POST['return'] ?? 'index.php?module=bag';
    }

    public function index()
    {
        Auth::requireSuperAdmin();
        $bags = $this->bag->getAllIncludingDeleted();
        $bagsWithVerifiedPayments = $this->getBagsWithVerifiedPayments();
        require __DIR__ . '/../views/bags/list.php';
    }

    private function getBagsWithVerifiedPayments()
    {
        $query = "SELECT DISTINCT u.bag_id FROM savings s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.status = 'verified' AND s.is_active = 1 AND s.deleted_at IS NULL 
                  AND u.status = 1 AND u.deleted_at IS NULL AND u.bag_id IS NOT NULL";
        $stmt = $this->bag->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }

    public function store()
    {
        Auth::requireSuperAdmin();
        $returnUrl = $this->getReturnUrl();

        $this->bag->name = trim($_POST['name'] ?? '');
        $this->bag->long_name = !empty($_POST['long_name']) ? trim($_POST['long_name']) : null;
        $this->bag->description = !empty($_POST['description']) ? trim($_POST['description']) : null;
        $this->bag->status = !empty($_POST['status']) ? (int)$_POST['status'] : 1;

        if (!Bag::isValidName($this->bag->name)) {
            header('Location: index.php?module=bag&action=create&toast=error&message=' . urlencode(Locale::get('group_name_invalid')));
            exit;
        }

        if ($this->bag->isNameTaken($this->bag->name)) {
            header('Location: index.php?module=bag&action=create&toast=error&message=' . urlencode(Locale::get('bag_name_taken')));
            exit;
        }

        // Handle picture upload
        $this->bag->picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->bag->uploadPicture($_FILES['picture']);
            if ($uploaded === false) {
                header('Location: index.php?module=bag&action=create&toast=error&message=' . urlencode(Locale::get('invalid_file')));
                exit;
            }
            $this->bag->picture = $uploaded;
        }

        if ($this->bag->create()) {
            // Add superadmin to the new group
            $this->bag->addUserToBag(Auth::getUserId(), $this->bag->id);
            
            ActivityLog::log('bag_created', null, null,
                ['bag_id' => $this->bag->id, 'name' => $this->bag->name]);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('created_successfully')));
            exit;
        }
        header('Location: index.php?module=bag&action=create&toast=error&message=' . urlencode(Locale::get('error_creating')));
        exit;
    }

    public function update($id)
    {
        Auth::requireSuperAdmin();
        $returnUrl = $this->getReturnUrl();

        $existingBag = $this->bag->getByIdIncludingDeleted($id);
        if (!$existingBag) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('group_not_found')));
            exit;
        }

        $this->bag->id = $id;
        $this->bag->name = trim($_POST['name'] ?? '');
        $this->bag->long_name = !empty($_POST['long_name']) ? trim($_POST['long_name']) : null;
        $this->bag->description = !empty($_POST['description']) ? trim($_POST['description']) : null;
        $this->bag->picture = $existingBag['picture'] ?? null;

        if (!Bag::isValidName($this->bag->name)) {
            header('Location: index.php?module=bag&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('group_name_invalid')));
            exit;
        }

        // Prevent status change if bag has verified payments
        $newStatus = !empty($_POST['status']) ? (int)$_POST['status'] : 1;
        if ($this->hasVerifiedPayments($id) && $newStatus !== (int)$existingBag['status']) {
            header('Location: index.php?module=bag&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('bag_cannot_change_status')));
            exit;
        }
        $this->bag->status = $newStatus;

        if ($this->bag->isNameTaken($this->bag->name, $id)) {
            header('Location: index.php?module=bag&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('bag_name_taken')));
            exit;
        }

        // Handle picture upload
        $pictureResult = null;
        $pictureError = null;
        
        if (!isset($_FILES['picture'])) {
            $pictureError = 'no_file_input';
            error_log("Bag update: no_file_input");
        } elseif ($_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $pictureError = 'no_file_selected';
            error_log("Bag update: no_file_selected");
        } elseif ($_FILES['picture']['error'] !== UPLOAD_ERR_OK) {
            $pictureError = 'upload_error_' . $_FILES['picture']['error'];
            error_log("Bag update: upload_error=" . $_FILES['picture']['error']);
        } else {
            // File was uploaded
            $file = $_FILES['picture'];
            error_log("Bag update: User $id file={$file['name']} size={$file['size']} type={$file['type']}");
            
            // Delete old picture
            if ($this->bag->picture) {
                $this->bag->deletePicture($this->bag->picture);
            }
            
            $uploaded = $this->bag->uploadPicture($file);
            if ($uploaded === false) {
                $pictureError = 'validation_failed';
                error_log("Bag update: Validation failed for bag $id");
            } else {
                $this->bag->picture = $uploaded;
                $pictureResult = $uploaded;
                error_log("Bag update: Success - $uploaded");
            }
        }

        if (isset($_POST['remove_picture']) && $_POST['remove_picture'] === '1') {
            if ($this->bag->picture) {
                $this->bag->deletePicture($this->bag->picture);
                $this->bag->picture = null;
            }
        }

        // Build changes
        $changes = [];
        if ($existingBag['name'] !== $this->bag->name) {
            $changes['Name'] = ['old' => $existingBag['name'], 'new' => $this->bag->name];
        }
        if (($existingBag['long_name'] ?? '') !== $this->bag->long_name) {
            $changes['Long Name'] = ['old' => $existingBag['long_name'] ?? '', 'new' => $this->bag->long_name];
        }
        if (($existingBag['description'] ?? '') !== $this->bag->description) {
            $changes['Description'] = ['old' => $existingBag['description'] ?? '', 'new' => $this->bag->description];
        }
        if ((int)$existingBag['status'] !== $this->bag->status) {
            $changes['Status'] = ['old' => $existingBag['status'], 'new' => $this->bag->status];
        }
        if ($pictureResult) {
            $changes['Picture'] = ['old' => $existingBag['picture'] ?? 'none', 'new' => $pictureResult];
        } elseif ($pictureError && $pictureError !== 'no_file_selected' && $pictureError !== 'no_file_input') {
            $changes['Picture'] = ['old' => $existingBag['picture'] ?? 'none', 'new' => 'FAILED: ' . $pictureError];
        }

        // Build log payload
        $logPayload = [
            'bag_id' => $id,
            'name' => $this->bag->name,
            'picture_status' => $pictureResult ? 'uploaded' : ($pictureError ?: 'none'),
        ];
        if ($pictureResult) {
            $logPayload['picture'] = $pictureResult;
            $logPayload['thumbnail'] = preg_replace('/\.[^.]+$/', '_thumb.jpg', $pictureResult);
        }

        if ($this->bag->update()) {
            ActivityLog::log('bag_updated', null, null,
                $logPayload,
                !empty($changes) ? $changes : null);
            header('Location: ' . $returnUrl . '&toast=success&message=' . urlencode(Locale::get('updated_successfully')));
            exit;
        }
        header('Location: index.php?module=bag&action=edit&id=' . $id . '&toast=error&message=' . urlencode(Locale::get('error_updating')));
        exit;
    }

    public function disable($id)
    {
        Auth::requireSuperAdmin();

        $bag = $this->bag->getByIdIncludingDeleted($id);
        if (!$bag) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('bag_not_found')));
            exit;
        }

        if ($this->bag->delete($id)) {
            ActivityLog::log('bag_disabled', null, null,
                ['bag_id' => $id, 'name' => $bag['name']]);
            header('Location: index.php?module=bag&toast=success&message=' . urlencode(Locale::get('disabled_successfully')));
            exit;
        }
        header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('error_disabling')));
        exit;
    }

    public function truncate($id)
    {
        Auth::requireSuperAdmin();

        $bag = $this->bag->getByIdIncludingDeleted($id);
        if (!$bag) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('group_not_found')));
            exit;
        }

        // Check if this is the last bag
        $allBags = $this->bag->getAllIncludingDeleted();
        $bagCount = $allBags->rowCount();
        if ($bagCount <= 1) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('cannot_truncate_last_group')));
            exit;
        }

        // Get stats before truncating
        $stats = $this->getTruncateStats($id);
        
        // Create dump file
        $dumpPath = $this->createDumpFile($id, $bag, $stats);
        if (!$dumpPath) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('error_creating_dump')));
            exit;
        }

        // Truncate all records
        $this->executeTruncate($id);

        // Log the operation
        ActivityLog::log('bag_truncated', null, null, [
            'bag_id' => $id,
            'name' => $bag['name'],
            'dump_file' => basename($dumpPath),
            'stats' => $stats
        ]);

        // Store download info in session for the list page
        $_SESSION['bag_truncate_download'] = [
            'filepath' => $dumpPath,
            'filename' => basename($dumpPath),
            'bag_name' => $bag['name']
        ];

        // Redirect to list with success message
        header('Location: index.php?module=bag&toast=success&message=' . urlencode(Locale::get('bag_truncated_successfully')));
        exit;
    }

    private function getTruncateStats($bagId)
    {
        $tables = [
            'savings' => 'bag_id',
            'expenses' => 'bag_id',
            'activity_logs' => 'bag_id',
            'activities' => 'bag_id',
            'bag_user' => 'bag_id',
            'users' => 'bag_id'
        ];

        $stats = [];
        foreach ($tables as $table => $column) {
            $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :bag_id";
            $stmt = $this->bag->conn->prepare($query);
            $stmt->bindParam(':bag_id', $bagId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$table] = (int)$result['count'];
        }

        return $stats;
    }

    private function createDumpFile($bagId, $bag, $stats)
    {
        $dumpDir = __DIR__ . '/../uploads/dumps/';
        if (!is_dir($dumpDir)) {
            mkdir($dumpDir, 0755, true);
        }

        $filename = $bag['name'] . '_truncate_' . date('Y-m-d_His') . '.sql';
        $filepath = $dumpDir . $filename;

        $handle = fopen($filepath, 'w');
        if (!$handle) {
            return false;
        }

        // Write header
        fwrite($handle, "-- =============================================\n");
        fwrite($handle, "-- Group Truncate Dump\n");
        fwrite($handle, "-- Group ID: {$bagId}\n");
        fwrite($handle, "-- Group Name: {$bag['name']}\n");
        fwrite($handle, "-- Group Display Name: " . ($bag['long_name'] ?? 'N/A') . "\n");
        fwrite($handle, "-- Group Description: " . ($bag['description'] ?? 'N/A') . "\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- =============================================\n\n");

        // Write stats
        fwrite($handle, "-- Record counts before truncation:\n");
        foreach ($stats as $table => $count) {
            fwrite($handle, "-- {$table}: {$count} records\n");
        }
        fwrite($handle, "\n");

        // Write bag record first (so it can be recreated)
        fwrite($handle, "-- =============================================\n");
        fwrite($handle, "-- Table: bags (group record)\n");
        fwrite($handle, "-- =============================================\n\n");
        
        fwrite($handle, "-- Table structure\n");
        $bagStructQuery = "SHOW CREATE TABLE bags";
        $bagStructStmt = $this->bag->conn->query($bagStructQuery);
        $bagStructRow = $bagStructStmt->fetch(PDO::FETCH_ASSOC);
        fwrite($handle, $bagStructRow['Create Table'] . ";\n\n");
        
        fwrite($handle, "-- Group record (insert this first)\n");
        $bagColumns = ['id', 'name', 'long_name', 'description', 'picture', 'status', 'created_at', 'updated_at'];
        $bagValues = [
            $bag['id'],
            "'" . addslashes($bag['name']) . "'",
            $bag['long_name'] ? "'" . addslashes($bag['long_name']) . "'" : 'NULL',
            $bag['description'] ? "'" . addslashes($bag['description']) . "'" : 'NULL',
            $bag['picture'] ? "'" . addslashes($bag['picture']) . "'" : 'NULL',
            $bag['status'],
            "'" . $bag['created_at'] . "'",
            "'" . $bag['updated_at'] . "'"
        ];
        fwrite($handle, "INSERT INTO `bags` (`" . implode('`, `', $bagColumns) . "`) VALUES (" . implode(', ', $bagValues) . ");\n\n");

        // Write structure and data for each table
        $tables = ['users', 'savings', 'activities', 'expenses', 'activity_logs', 'bag_user'];
        
        foreach ($tables as $table) {
            $query = "SELECT * FROM {$table} WHERE bag_id = :bag_id";
            $stmt = $this->bag->conn->prepare($query);
            $stmt->bindParam(':bag_id', $bagId);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                continue;
            }

            // Get table structure
            $structQuery = "SHOW CREATE TABLE {$table}";
            $structStmt = $this->bag->conn->query($structQuery);
            $structRow = $structStmt->fetch(PDO::FETCH_ASSOC);
            
            fwrite($handle, "-- =============================================\n");
            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "-- Records: " . count($rows) . "\n");
            fwrite($handle, "-- =============================================\n\n");
            
            // Write CREATE TABLE statement
            fwrite($handle, "-- Table structure\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}_backup`;\n");
            fwrite($handle, str_replace("CREATE TABLE `{$table}`", "CREATE TABLE `{$table}_backup`", $structRow['Create Table']) . ";\n\n");
            
            // Write INSERT statements
            fwrite($handle, "-- Data\n");
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_map(function($v) use ($row) {
                    return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                }, array_values($row));
                
                $sql = "INSERT INTO `{$table}_backup` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                fwrite($handle, $sql);
            }
            fwrite($handle, "\n");
        }

        fclose($handle);
        return $filepath;
    }

    private function executeTruncate($bagId)
    {
        // Delete in correct order to respect foreign keys
        $tables = [
            'expenses' => 'bag_id',
            'activity_logs' => 'bag_id',
            'savings' => 'bag_id',
            'activities' => 'bag_id',
            'bag_user' => 'bag_id',
            'users' => 'bag_id'
        ];

        foreach ($tables as $table => $column) {
            $query = "DELETE FROM {$table} WHERE {$column} = :bag_id";
            $stmt = $this->bag->conn->prepare($query);
            $stmt->bindParam(':bag_id', $bagId);
            $stmt->execute();
        }

        // Delete the bag itself
        $query = "DELETE FROM bags WHERE id = :bag_id";
        $stmt = $this->bag->conn->prepare($query);
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->execute();
    }

    private function downloadDumpFile($filepath, $bagName)
    {
        if (!file_exists($filepath)) {
            return;
        }

        $filename = basename($filepath);
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    private function hasVerifiedPayments($bagId)
    {
        $query = "SELECT COUNT(*) as count FROM savings s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE u.bag_id = :bag_id AND s.status = 'verified' AND s.is_active = 1 AND s.deleted_at IS NULL 
                  AND u.status = 1 AND u.deleted_at IS NULL";
        $stmt = $this->bag->conn->prepare($query);
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getJson($id)
    {
        if (!Auth::isLoggedIn() || !Auth::isSuperAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            exit;
        }

        header('Content-Type: application/json');

        $bag = $this->bag->getByIdIncludingDeleted($id);
        if (!$bag) {
            echo json_encode(['error' => 'not_found']);
            exit;
        }

        echo json_encode($bag);
        exit;
    }

    public function downloadDump()
    {
        Auth::requireSuperAdmin();

        if (!isset($_SESSION['bag_truncate_download'])) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('dump_not_found')));
            exit;
        }

        $download = $_SESSION['bag_truncate_download'];
        unset($_SESSION['bag_truncate_download']);

        $filepath = $download['filepath'];
        $filename = $download['filename'];

        if (!file_exists($filepath)) {
            header('Location: index.php?module=bag&toast=error&message=' . urlencode(Locale::get('dump_not_found')));
            exit;
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}
