<?php

require_once __DIR__ . '/../config/database.php';

class Saving
{
    private $conn;
    private $table = 'savings';

    public $id;
    public $user_id;
    public $name;
    public $amount;
    public $payment_method;
    public $status;
    public $description;
    public $attachment;
    public $created_at;
    public $updated_at;

    private $uploadDir = __DIR__ . '/../uploads/';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        $query = "SELECT s.*, u.firstname, u.lastname, u.nickname, u.picture 
                  FROM {$this->table} s 
                  LEFT JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.is_active = 1 AND s.deleted_at IS NULL
                  ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT s.*, u.firstname, u.lastname, u.nickname, u.picture 
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

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
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
        $query = "INSERT INTO {$this->table} (user_id, name, amount, payment_method, status, description, attachment, created_at) 
                  VALUES (:user_id, :name, :amount, :payment_method, :status, :description, :attachment, :created_at)";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':description', $this->description);
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
                  SET user_id = :user_id, name = :name, amount = :amount, payment_method = :payment_method, 
                      status = :status, description = :description, attachment = :attachment, created_at = :created_at
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':description', $this->description);
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

    public function getTotalSavings()
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM {$this->table} WHERE status = 'completed' AND is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
