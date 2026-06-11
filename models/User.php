<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $firstname;
    public $lastname;
    public $nickname;
    public $telephone;
    public $picture;
    public $comments;
    public $multiplier;
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
        $query = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id AND status = 1 AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function uploadPicture($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

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
        $filename = uniqid('user_', true) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }

        return false;
    }

    public function deletePicture($filename)
    {
        if ($filename && file_exists($this->uploadDir . $filename)) {
            unlink($this->uploadDir . $filename);
        }
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (firstname, lastname, nickname, telephone, picture, comments, multiplier) 
                  VALUES (:firstname, :lastname, :nickname, :telephone, :picture, :comments, :multiplier)";

        $stmt = $this->conn->prepare($query);

        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->nickname = htmlspecialchars(strip_tags($this->nickname));
        $this->comments = htmlspecialchars(strip_tags($this->comments));

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':nickname', $this->nickname);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':multiplier', $this->multiplier);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET firstname = :firstname, lastname = :lastname, nickname = :nickname, 
                      telephone = :telephone, picture = :picture, comments = :comments, multiplier = :multiplier
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->nickname = htmlspecialchars(strip_tags($this->nickname));
        $this->comments = htmlspecialchars(strip_tags($this->comments));

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':nickname', $this->nickname);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':multiplier', $this->multiplier);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "UPDATE {$this->table} SET status = 0, deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
