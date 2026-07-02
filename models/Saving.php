<?php

require_once __DIR__ . '/../config/database.php';

class Saving
{
    private $conn;
    private $table = 'savings';

    public $id;
    public $user_id;
    public $bag_id;
    public $description;
    public $amount;
    public $payment_method;
    public $status;
    public $notes;
    public $attachment;
    public $created_at;
    public $updated_at;

    private $uploadDir = __DIR__ . '/../uploads/';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($filters = [], $bagId = null)
    {
        $query = "SELECT s.*, u.firstname, u.lastname, u.username, u.picture 
                  FROM {$this->table} s 
                  INNER JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.is_active = 1 AND s.deleted_at IS NULL";
        
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND s.bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND s.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['payment_method'])) {
            $query .= " AND s.payment_method = :payment_method";
            $params[':payment_method'] = $filters['payment_method'];
        }
        
        if (!empty($filters['month'])) {
            $query .= " AND YEAR(s.created_at) = :year AND MONTH(s.created_at) = :month";
            $date = explode('-', $filters['month']);
            $params[':year'] = $date[0];
            $params[':month'] = $date[1];
        }
        
        $query .= " ORDER BY s.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT s.*, u.firstname, u.lastname, u.username, u.picture 
                  FROM {$this->table} s 
                  LEFT JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.id = :id AND s.is_active = 1 AND s.deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function uploadAttachment($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

        // Server-side MIME type validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realType = $finfo->file($file['tmp_name']);
        if (!in_array($realType, $allowedTypes)) {
            return false;
        }

        // Extension validation
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        $maxSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $filename = uniqid('attachment_', true) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }

        return false;
    }

    public function deleteAttachment($filename)
    {
        if ($filename && file_exists($this->uploadDir . $filename)) {
            unlink($this->uploadDir . $filename);
        }
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (user_id, bag_id, description, amount, payment_method, status, notes, attachment, created_at) 
                  VALUES (:user_id, :bag_id, :description, :amount, :payment_method, :status, :notes, :attachment, :created_at)";

        $stmt = $this->conn->prepare($query);

        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':bag_id', $this->bag_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':attachment', $this->attachment);
        $stmt->bindParam(':created_at', $this->created_at);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET user_id = :user_id, description = :description, amount = :amount, payment_method = :payment_method, 
                      status = :status, notes = :notes, attachment = :attachment, created_at = :created_at
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':attachment', $this->attachment);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "UPDATE {$this->table} SET is_active = 0, deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTotalSavings($userId = null, $bagId = null)
    {
        $query = "SELECT COALESCE(SUM(s.amount), 0) as total 
                  FROM {$this->table} s
                  INNER JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.status = 'verified' AND s.is_active = 1 AND s.deleted_at IS NULL";
        
        $params = [];
        
        if ($userId !== null) {
            $query .= " AND s.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if ($bagId !== null) {
            $query .= " AND s.bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
