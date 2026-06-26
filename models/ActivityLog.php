<?php

require_once __DIR__ . '/../config/database.php';

class ActivityLog
{
    private $conn;
    private $table = 'activity_logs';

    public $id;
    public $user_id;
    public $username;
    public $record_owner_id;
    public $record_owner_name;
    public $action;
    public $payload;
    public $changes;
    public $ip_address;

    private $sensitiveFields = [
        'password', 'new_password', 'current_password', 'confirm_password',
        'db_password', 'db_root_password', 'ssh_pass', 'secret', 'token',
        'api_key', 'credit_card', 'cvv', 'ssn'
    ];

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public static function log($action, $recordOwnerId = null, $recordOwnerName = null, $payload = null, $changes = null)
    {
        $instance = new self();
        
        // Action user = logged in user (who performed the action)
        $instance->user_id = Auth::getUserId() ?? null;
        $instance->username = Auth::getUserName() ?: null;
        
        // Record owner = user who owns the record being modified
        $instance->record_owner_id = $recordOwnerId;
        $instance->record_owner_name = $recordOwnerName;
        
        $instance->action = $action;
        $instance->payload = $payload ? $instance->sanitize($payload) : null;
        $instance->changes = $changes ? $instance->sanitize($changes) : null;
        $instance->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        return $instance->create();
    }

    private function sanitize($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true) ?? $data;
        }

        if (is_array($data)) {
            $data = $this->removeSensitiveFields($data);
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }

    private function removeSensitiveFields($data)
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $this->sensitiveFields)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->removeSensitiveFields($value);
            }
        }
        return $data;
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table} (user_id, username, record_owner_id, record_owner_name, action, payload, changes, ip_address) 
                  VALUES (:user_id, :username, :record_owner_id, :record_owner_name, :action, :payload, :changes, :ip_address)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':record_owner_id', $this->record_owner_id);
        $stmt->bindParam(':record_owner_name', $this->record_owner_name);
        $stmt->bindParam(':action', $this->action);
        $stmt->bindParam(':payload', $this->payload);
        $stmt->bindParam(':changes', $this->changes);
        $stmt->bindParam(':ip_address', $this->ip_address);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = "al.action = :action";
            $params[':action'] = $filters['action'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT al.*, 
                         CONCAT(u.firstname, ' ', u.lastname) as user_fullname,
                         u.role as user_role
                  FROM {$this->table} al
                  LEFT JOIN users u ON al.user_id = u.id
                  {$whereClause}
                  ORDER BY al.created_at DESC
                  LIMIT 500";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getDistinctActions()
    {
        $query = "SELECT DISTINCT action FROM {$this->table} ORDER BY action";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctUsers()
    {
        $query = "SELECT DISTINCT al.user_id, al.username, 
                   CONCAT(u.firstname, ' ', u.lastname) as fullname
                  FROM {$this->table} al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.user_id IS NOT NULL
                  ORDER BY fullname";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
