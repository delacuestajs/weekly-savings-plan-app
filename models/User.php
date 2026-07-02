<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $firstname;
    public $lastname;
    public $username;
    public $telephone;
    public $picture;
    public $comments;
    public $multiplier;
    public $role;
    public $bag_id;
    public $password;
    public $created_at;
    public $updated_at;

    private $uploadDir = __DIR__ . '/../uploads/';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public static function getDefaultPassword()
    {
        return getenv('DEFAULT_PASSWORD') ?: 'abcd1234';
    }

    public static function isDefaultPassword($password)
    {
        return password_verify(self::getDefaultPassword(), $password);
    }

    public function getAll($bagId = null, $includeSuperAdmin = false)
    {
        $query = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL";
        
        if (!$includeSuperAdmin) {
            $query .= " AND role != 3";
        }
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($bagId !== null) {
            $stmt->bindParam(':bag_id', $bagId);
        }
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

    public function authenticate($login, $password, $bagId = null)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE (username = :login OR firstname = :login) 
                  AND status = 1 AND deleted_at IS NULL AND role > 0";
        
        $params = [':login' => $login];
        
        // Filter by bag_id for non-superadmin users
        if ($bagId !== null) {
            $query .= " AND (bag_id = :bag_id OR role = 3)";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // For superadmin, verify they belong to the selected bag via pivot table
            if ($bagId !== null && $user['role'] == 3) {
                $bag = new Bag();
                if (!$bag->userBelongsToBag($user['id'], $bagId)) {
                    return false;
                }
            }
            return $user;
        }
        return false;
    }

    public function belongsToBag($userId, $bagId)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} 
                  WHERE id = :id AND bag_id = :bag_id AND status = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function isAdmin($userId)
    {
        $user = $this->getById($userId);
        return $user && ($user['role'] ?? 0) >= 2;
    }

    public function isSuperAdmin($userId)
    {
        $user = $this->getById($userId);
        return $user && ($user['role'] ?? 0) == 3;
    }

    public function uploadPicture($file)
    {
        error_log("uploadPicture called: " . json_encode($file));
        
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("uploadPicture: file error or not set, error=" . ($file['error'] ?? 'null'));
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Server-side MIME type validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realType = $finfo->file($file['tmp_name']);
        error_log("uploadPicture: MIME type=$realType");
        if (!in_array($realType, $allowedTypes)) {
            error_log("uploadPicture: invalid MIME type");
            return false;
        }

        // Extension validation
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            error_log("uploadPicture: invalid extension=$extension");
            return false;
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            error_log("uploadPicture: file too large=" . $file['size']);
            return false;
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $filename = uniqid('user_', true) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        error_log("uploadPicture: saving to $filepath");

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("uploadPicture: file saved successfully");
            // Create 1:1 cropped thumbnail
            $this->createThumbnail($filepath, $extension);
            return $filename;
        }

        error_log("uploadPicture: move_uploaded_file failed");
        return false;
    }

    private function createThumbnail($filepath, $extension)
    {
        $thumbSize = 128; // Thumbnail size in pixels
        $thumbPath = preg_replace('/\.[^.]+$/', '_thumb.jpg', $filepath);
        
        error_log("Creating thumbnail: $filepath -> $thumbPath");
        
        // Load image based on type
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
            error_log("Failed to load image: $filepath (extension: $extension)");
            return false;
        }
        
        $width = imagesx($source);
        $height = imagesy($source);
        error_log("Image dimensions: {$width}x{$height}");
        
        // Calculate 1:1 crop
        $size = min($width, $height);
        $x = intval(($width - $size) / 2);
        $y = intval(($height - $size) / 2);
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
        imagecopyresampled($thumb, $source, 0, 0, $x, $y, $thumbSize, $thumbSize, $size, $size);
        
        // Save as JPEG
        $result = imagejpeg($thumb, $thumbPath, 85);
        error_log("Thumbnail saved: $thumbPath (result: " . ($result ? 'true' : 'false') . ")");
        
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
        return 'uploads/' . $thumbFilename;
    }

    public static function getPictureUrl($picture)
    {
        if (empty($picture)) {
            return null;
        }
        return 'uploads/' . $picture;
    }

    public function isUsernameTaken($username, $excludeId = null, $bagId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = [':username' => $username];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (firstname, lastname, username, telephone, picture, comments, multiplier, role, bag_id, password) 
                  VALUES (:firstname, :lastname, :username, :telephone, :picture, :comments, :multiplier, :role, :bag_id, :password)";

        $stmt = $this->conn->prepare($query);

        $this->firstname = htmlspecialchars(strip_tags($this->firstname ?? ''));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname ?? ''));
        $this->username = $this->username ? htmlspecialchars(strip_tags($this->username)) : null;
        $this->comments = $this->comments ? htmlspecialchars(strip_tags($this->comments)) : null;

        $hashedPassword = !empty($this->password) ? password_hash($this->password, PASSWORD_DEFAULT) : password_hash(self::getDefaultPassword(), PASSWORD_DEFAULT);

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':multiplier', $this->multiplier);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':bag_id', $this->bag_id);
        $stmt->bindParam(':password', $hashedPassword);

        try {
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch (PDOException $e) {
            // Duplicate username in same bag
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET firstname = :firstname, lastname = :lastname, username = :username, 
                      telephone = :telephone, picture = :picture, comments = :comments, 
                      multiplier = :multiplier, role = :role, bag_id = :bag_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->firstname = htmlspecialchars(strip_tags($this->firstname ?? ''));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname ?? ''));
        $this->username = $this->username ? htmlspecialchars(strip_tags($this->username)) : null;
        $this->comments = $this->comments ? htmlspecialchars(strip_tags($this->comments)) : null;

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':picture', $this->picture);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':multiplier', $this->multiplier);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':bag_id', $this->bag_id);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    public function resetPassword($id)
    {
        $hashedPassword = password_hash(self::getDefaultPassword(), PASSWORD_DEFAULT);
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function changePassword($id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);
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
