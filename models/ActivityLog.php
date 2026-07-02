<?php

require_once __DIR__ . '/../config/database.php';

class ActivityLog
{
    private $conn;
    private $table = 'activity_logs';

    public $id;
    public $user_id;
    public $bag_id;
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

    public static function log($action, $recordOwnerId = null, $recordOwnerName = null, $payload = null, $changes = null, $actionUserId = null, $actionUsername = null)
    {
        $instance = new self();
        
        // Action user = logged in user (who performed the action)
        // Use provided values or fall back to Auth
        $instance->user_id = $actionUserId ?? Auth::getUserId() ?? null;
        $instance->username = $actionUsername ?? Auth::getUserName() ?: null;
        $instance->bag_id = Auth::getBagId() ?? null;
        
        // Record owner = user who owns the record being modified
        $instance->record_owner_id = $recordOwnerId;
        $instance->record_owner_name = $recordOwnerName;
        
        $instance->action = $action;
        $instance->payload = $payload ? $instance->sanitize($payload) : null;
        $instance->changes = $changes ? $instance->sanitize($changes) : null;
        // Get real client IP (works behind reverse proxy like Caddy)
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        $realIp = $_SERVER['HTTP_X_REAL_IP'] ?? null;
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        
        if ($forwardedFor) {
            // X-Forwarded-For contains: client, proxy1, proxy2
            // First IP is the original client
            $ips = array_map('trim', explode(',', $forwardedFor));
            $instance->ip_address = $ips[0];
            // Store full chain if multiple IPs
            if (count($ips) > 1) {
                $instance->ip_address .= ' (via ' . $ips[1] . ')';
            }
        } elseif ($realIp) {
            $instance->ip_address = $realIp;
        } else {
            $instance->ip_address = $remoteAddr;
        }

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
        $query = "INSERT INTO {$this->table} (user_id, bag_id, username, record_owner_id, record_owner_name, action, payload, changes, ip_address) 
                  VALUES (:user_id, :bag_id, :username, :record_owner_id, :record_owner_name, :action, :payload, :changes, :ip_address)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':bag_id', $this->bag_id);
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

    public function getAll($filters = [], $bagId = null)
    {
        $where = [];
        $params = [];

        if ($bagId !== null) {
            $where[] = "al.bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        } else {
            // When no specific bag, only show logs with NULL bag_id (system logs)
            // This prevents showing logs from all bags to superadmin without bag context
            $where[] = "al.bag_id IS NULL";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            if ($filters['user_id'] === '0') {
                // Filter for system actions (no user)
                $where[] = "al.user_id IS NULL";
            } else {
                $where[] = "al.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
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

    public function getDistinctActions($bagId = null)
    {
        $query = "SELECT DISTINCT action FROM {$this->table}";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " WHERE bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " ORDER BY action";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctUsers($bagId = null)
    {
        $query = "SELECT al.user_id, 
                         COALESCE(MAX(CONCAT(u.firstname, ' ', u.lastname)), MAX(al.username)) as fullname
                  FROM {$this->table} al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.user_id IS NOT NULL";
        
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND al.bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " GROUP BY al.user_id ORDER BY fullname";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
