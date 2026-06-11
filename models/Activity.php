<?php

require_once __DIR__ . '/../config/database.php';

class Activity
{
    private $conn;
    private $table = 'activities';

    public $id;
    public $name;
    public $description;
    public $value;
    public $activity_date;
    public $is_active;
    public $deleted_at;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 AND deleted_at IS NULL
                  ORDER BY activity_date DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
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

    public function getByYear($year)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE YEAR(activity_date) = :year AND is_active = 1 AND deleted_at IS NULL
                  ORDER BY activity_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByMonth($year, $month)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE YEAR(activity_date) = :year AND MONTH(activity_date) = :month 
                  AND is_active = 1 AND deleted_at IS NULL
                  ORDER BY activity_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalByYear($year)
    {
        $query = "SELECT COALESCE(SUM(value), 0) as total 
                  FROM {$this->table} 
                  WHERE YEAR(activity_date) = :year AND is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (name, description, value, activity_date, created_at) 
                  VALUES (:name, :description, :value, :activity_date, :created_at)";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':value', $this->value);
        $stmt->bindParam(':activity_date', $this->activity_date);
        $stmt->bindParam(':created_at', $this->created_at);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE {$this->table} 
                  SET name = :name, description = :description, value = :value, 
                      activity_date = :activity_date, updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':value', $this->value);
        $stmt->bindParam(':activity_date', $this->activity_date);
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
}
