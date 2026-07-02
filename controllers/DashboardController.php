<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Activity.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/WeeklySaving.php';
require_once __DIR__ . '/../controllers/Auth.php';

class DashboardController
{
    private $conn;
    private $saving;
    private $user;
    private $activity;
    private $log;
    private $weeklySaving;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->saving = new Saving();
        $this->user = new User();
        $this->activity = new Activity();
        $this->log = new ActivityLog();
        $this->weeklySaving = new WeeklySaving();
    }

    public function index()
    {
        Auth::requireAdmin();

        $bagId = Auth::getBagId();

        // Weekly plan summary - use WeeklySaving model for consistency
        $weeklyOverview = $this->weeklySaving->getWeeklyOverview(date('Y'), null, $bagId);
        $weeklyData = $this->getWeeklySummary($weeklyOverview);

        // Payments summary
        $totalPayments = $this->saving->getTotalSavings(null, $bagId);
        $paymentsCount = $this->getPaymentsCount($bagId);
        $recentPayments = $this->getRecentPayments($bagId);

        // Users summary - last 3 created (exclude superadmin)
        $recentUsers = $this->getRecentUsers($bagId);
        $usersCount = $this->getUsersCount($bagId);
        $activeUsers = $this->getActiveUsers($bagId);

        // Activities summary
        $activitiesTotal = $this->activity->getTotalByYear(date('Y'), $bagId);
        $activitiesCount = $this->getActivitiesCount($bagId);

        require __DIR__ . '/../views/dashboard.php';
    }

    private function getWeeklySummary($weeklyOverview)
    {
        $currentWeek = $this->weeklySaving->getCurrentWeekNumber(date('Y'));
        $grouped = $weeklyOverview['grouped'];
        
        // Count paid, partial, and pending weeks
        $paidWeeks = 0;
        $partialWeeks = 0;
        $pendingWeeks = 0;
        
        foreach ($grouped as $monthData) {
            foreach ($monthData['weeks'] as $week) {
                if ($week['paid']) {
                    $paidWeeks++;
                } elseif ($week['partial']) {
                    $partialWeeks++;
                } else {
                    $pendingWeeks++;
                }
            }
        }
        
        return [
            'year_goal' => $weeklyOverview['total_year_goal_with_activities'],
            'total_paid' => $weeklyOverview['total_paid'],
            'pending' => $weeklyOverview['total_pending'],
            'percent' => $weeklyOverview['progress_percent'],
            'current_week' => $currentWeek,
            'paid_weeks' => $paidWeeks,
            'partial_weeks' => $partialWeeks,
            'pending_weeks' => $pendingWeeks,
            'multiplier' => $weeklyOverview['multiplier'],
        ];
    }

    private function getPaymentsCount($bagId = null)
    {
        $query = "SELECT COUNT(*) as count FROM savings s
                  INNER JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.is_active = 1 AND s.deleted_at IS NULL";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    private function getRecentPayments($bagId = null)
    {
        $query = "SELECT s.*, u.firstname, u.lastname 
                  FROM savings s 
                  INNER JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.is_active = 1 AND s.deleted_at IS NULL";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND s.bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " ORDER BY s.created_at DESC LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getRecentUsers($bagId = null)
    {
        $query = "SELECT * FROM users 
                  WHERE status = 1 AND deleted_at IS NULL AND role != 3";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT 3";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUsersCount($bagId = null)
    {
        $query = "SELECT COUNT(*) as count FROM users WHERE status = 1 AND deleted_at IS NULL AND role != 3";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    private function getActiveUsers($bagId = null)
    {
        $query = "SELECT COUNT(*) as count FROM users WHERE status = 1 AND deleted_at IS NULL AND role > 0 AND role != 3";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    private function getActivitiesCount($bagId = null)
    {
        $query = "SELECT COUNT(*) as count FROM activities WHERE is_active = 1 AND deleted_at IS NULL";
        $params = [];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
