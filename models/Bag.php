<?php

require_once __DIR__ . '/../config/database.php';

class Bag
{
    public $conn;
    private $table = 'bags';

    public $id;
    public $name;
    public $long_name;
    public $description;
    public $picture;
    public $status;
    public $fixed_amount;

    private $uploadDir = __DIR__ . '/../uploads/bags/';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllIncludingInactive()
    {
        $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllIncludingDeleted()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByIdIncludingDeleted($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id AND status = 1 AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isNameTaken($name, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name AND deleted_at IS NULL";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public static function isValidName($name)
    {
        // Only letters, numbers, hyphens, underscores - no spaces
        return preg_match('/^[a-zA-Z0-9_-]+$/', $name);
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (name, long_name, description, picture, status, fixed_amount) 
                  VALUES (:name, :long_name, :description, :picture, :status, :fixed_amount)";

        $stmt = $this->conn->prepare($query);

        $this->name = strip_tags($this->name ?? '');
        $this->long_name = $this->long_name ? strip_tags($this->long_name) : null;
        $this->description = $this->description ? strip_tags($this->description) : null;

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':long_name', $this->long_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':fixed_amount', $this->fixed_amount);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET name = :name, long_name = :long_name, description = :description, 
                      picture = :picture, status = :status, fixed_amount = :fixed_amount,
                      deleted_at = :deleted_at, updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = strip_tags($this->name ?? '');
        $this->long_name = $this->long_name ? strip_tags($this->long_name) : null;
        $this->description = $this->description ? strip_tags($this->description) : null;

        // Clear deleted_at when enabling (status=1), set when disabling (status=0)
        $deletedAt = ($this->status == 1) ? null : date('Y-m-d H:i:s');

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':long_name', $this->long_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':fixed_amount', $this->fixed_amount);
        $stmt->bindParam(':deleted_at', $deletedAt);
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

    public function getUsersByBagId($bagId)
    {
        $query = "SELECT COUNT(*) as count FROM users WHERE bag_id = :bag_id AND status = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getBagsByUserId($userId)
    {
        $query = "SELECT b.* FROM bags b 
                  INNER JOIN bag_user bu ON b.id = bu.bag_id 
                  WHERE bu.user_id = :user_id AND b.status = 1 AND b.deleted_at IS NULL 
                  ORDER BY b.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addUserToBag($userId, $bagId)
    {
        $query = "INSERT IGNORE INTO bag_user (user_id, bag_id) VALUES (:user_id, :bag_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':bag_id', $bagId);
        return $stmt->execute();
    }

    public function removeUserFromBag($userId, $bagId)
    {
        $query = "DELETE FROM bag_user WHERE user_id = :user_id AND bag_id = :bag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':bag_id', $bagId);
        return $stmt->execute();
    }

    public function userBelongsToBag($userId, $bagId)
    {
        $query = "SELECT COUNT(*) as count FROM bag_user WHERE user_id = :user_id AND bag_id = :bag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function uploadPicture($file)
    {
        error_log("Bag uploadPicture called: " . json_encode($file));
        
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("Bag uploadPicture: file error or not set, error=" . ($file['error'] ?? 'null'));
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];

        // Server-side MIME type validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realType = $finfo->file($file['tmp_name']);
        error_log("Bag uploadPicture: MIME type=$realType");
        if (!in_array($realType, $allowedTypes)) {
            error_log("Bag uploadPicture: invalid MIME type");
            return false;
        }

        // Extension validation
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            error_log("Bag uploadPicture: invalid extension=$extension");
            return false;
        }

        $maxSize = 15 * 1024 * 1024; // 15MB for cellphone photos
        if ($file['size'] > $maxSize) {
            error_log("Bag uploadPicture: file too large=" . $file['size']);
            return false;
        }

        if (!is_dir($this->uploadDir)) {
            error_log("Bag uploadPicture: creating directory=" . $this->uploadDir);
            mkdir($this->uploadDir, 0755, true);
        }

        $filename = uniqid('bag_', true) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        error_log("Bag uploadPicture: saving to $filepath");

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("Bag uploadPicture: file saved successfully");
            // Create 1:1 cropped thumbnail
            $this->createThumbnail($filepath, $extension);
            return $filename;
        }

        error_log("Bag uploadPicture: move_uploaded_file failed");
        return false;
    }

    private function createThumbnail($filepath, $extension)
    {
        $thumbSize = 128;
        $thumbPath = preg_replace('/\.[^.]+$/', '_thumb.jpg', $filepath);

        $source = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source = @imagecreatefromjpeg($filepath);
                break;
            case 'png':
                $source = @imagecreatefrompng($filepath);
                break;
            case 'gif':
                $source = @imagecreatefromgif($filepath);
                break;
            case 'webp':
                $source = @imagecreatefromwebp($filepath);
                break;
        }

        if (!$source) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // Calculate 1:1 crop
        $size = min($width, $height);
        $x = intval(($width - $size) / 2);
        $y = intval(($height - $size) / 2);

        // Create thumbnail
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
        imagecopyresampled($thumb, $source, 0, 0, $x, $y, $thumbSize, $thumbSize, $size, $size);

        // Save as JPEG
        $result = imagejpeg($thumb, $thumbPath, 85);

        imagedestroy($source);
        imagedestroy($thumb);

        return $result;
    }

    public function deletePicture($filename)
    {
        if ($filename && file_exists($this->uploadDir . $filename)) {
            unlink($this->uploadDir . $filename);
        }
        // Also delete thumbnail
        $thumbPath = preg_replace('/\.[^.]+$/', '_thumb.jpg', $this->uploadDir . $filename);
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }

    public static function getThumbnailUrl($picture)
    {
        if (empty($picture)) {
            return null;
        }
        $thumbFilename = preg_replace('/\.[^.]+$/', '_thumb.jpg', $picture);
        return 'uploads/bags/' . $thumbFilename;
    }

    public static function getPictureUrl($picture)
    {
        if (empty($picture)) {
            return null;
        }
        return 'uploads/bags/' . $picture;
    }
}
