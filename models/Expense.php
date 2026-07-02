<?php

require_once __DIR__ . '/../config/database.php';

class Expense
{
    private $conn;
    private $table = 'expenses';

    public $id;
    public $activity_id;
    public $bag_id;
    public $description;
    public $amount;
    public $status;
    public $is_active;
    public $deleted_at;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($activityId = null)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 AND deleted_at IS NULL";
        
        $params = [];
        if ($activityId !== null) {
            $query .= " AND activity_id = :activity_id";
            $params[':activity_id'] = $activityId;
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE id = :id AND is_active = 1 AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByActivityId($activityId)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE activity_id = :activity_id AND is_active = 1 AND deleted_at IS NULL
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':activity_id', $activityId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalByActivityId($activityId)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM {$this->table} 
                  WHERE activity_id = :activity_id AND is_active = 1 AND deleted_at IS NULL AND status = 'confirmed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':activity_id', $activityId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllTotalByActivityId($activityId)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM {$this->table} 
                  WHERE activity_id = :activity_id AND is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':activity_id', $activityId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (activity_id, bag_id, description, amount, status) 
                  VALUES (:activity_id, :bag_id, :description, :amount, :status)";

        $stmt = $this->conn->prepare($query);

        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':activity_id', $this->activity_id);
        $stmt->bindParam(':bag_id', $this->bag_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET description = :description, amount = :amount, updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function confirm()
    {
        $query = "UPDATE {$this->table} 
                  SET status = 'confirmed', updated_at = NOW()
                  WHERE id = :id AND status = 'pending'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        // Check if expense is confirmed
        $expense = $this->getById($id);
        if ($expense && $expense['status'] === 'confirmed') {
            return false; // Cannot delete confirmed expenses
        }

        $query = "UPDATE {$this->table} SET is_active = 0, deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
