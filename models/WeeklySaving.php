<?php

require_once __DIR__ . '/../config/database.php';

class WeeklySaving
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function getMonthName($month)
    {
        $months = [
            1 => Locale::get('january'),
            2 => Locale::get('february'),
            3 => Locale::get('march'),
            4 => Locale::get('april'),
            5 => Locale::get('may'),
            6 => Locale::get('june'),
            7 => Locale::get('july'),
            8 => Locale::get('august'),
            9 => Locale::get('september'),
            10 => Locale::get('october'),
            11 => Locale::get('november'),
            12 => Locale::get('december'),
        ];
        return $months[$month] ?? date('F', mktime(0, 0, 0, $month, 1));
    }

    private function getShortMonthName($month)
    {
        $months = [
            1 => Locale::get('jan'),
            2 => Locale::get('feb'),
            3 => Locale::get('mar'),
            4 => Locale::get('apr'),
            5 => Locale::get('may_short'),
            6 => Locale::get('jun'),
            7 => Locale::get('jul'),
            8 => Locale::get('aug'),
            9 => Locale::get('sep'),
            10 => Locale::get('oct'),
            11 => Locale::get('nov'),
            12 => Locale::get('dec'),
        ];
        return $months[$month] ?? date('M', mktime(0, 0, 0, $month, 1));
    }

    public function getWeeklyOverview($year = null, $userId = null, $bagId = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $multiplier = 0;
        $usersMultipliers = [];
        
        if ($userId !== null) {
            $userQuery = "SELECT multiplier FROM users WHERE id = :id AND status = 1 AND deleted_at IS NULL";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':id', $userId);
            $userStmt->execute();
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $multiplier = max(1, (int)$user['multiplier']);
            }
        } else {
            // Exclude superadmin (role=3) from combined totals
            $usersQuery = "SELECT id, firstname, lastname, multiplier FROM users WHERE status = 1 AND deleted_at IS NULL AND role != 3";
            $usersParams = [];
            
            if ($bagId !== null) {
                $usersQuery .= " AND bag_id = :bag_id";
                $usersParams[':bag_id'] = $bagId;
            }
            
            $usersStmt = $this->conn->prepare($usersQuery);
            foreach ($usersParams as $key => $value) {
                $usersStmt->bindValue($key, $value);
            }
            $usersStmt->execute();
            while ($u = $usersStmt->fetch(PDO::FETCH_ASSOC)) {
                $usersMultipliers[] = $u;
            }
            if (!empty($usersMultipliers)) {
                $totalMultiplier = 0;
                foreach ($usersMultipliers as $u) {
                    $totalMultiplier += max(1, (int)$u['multiplier']);
                }
                $multiplier = $totalMultiplier;
            }
        }

        $totalPaid = $this->getTotalPaid($userId, $bagId);
        $weeks = [];

        $jan1 = new DateTime("$year-01-01");
        $dayOfWeek = (int)$jan1->format('w');
        
        if ($dayOfWeek == 0) {
            $dayOfWeek = 7;
        }
        
        $week1Start = clone $jan1;
        $week1Start->modify('-' . ($dayOfWeek - 1) . ' days');

        for ($week = 1; $week <= 52; $week++) {
            $weekValue = $week * 1000 * $multiplier;
            
            $weekStart = clone $week1Start;
            $weekStart->modify('+' . (($week - 1) * 7) . ' days');
            
            $weekEnd = clone $weekStart;
            $weekEnd->modify('+6 days');

            $month = $this->getWeekMonth($weekStart, $year);

            $weeks[] = [
                'week' => $week,
                'value' => $weekValue,
                'base_value' => $week * 1000,
                'multiplier' => $multiplier,
                'start_date' => $weekStart->format('Y-m-d'),
                'end_date' => $weekEnd->format('Y-m-d'),
                'month' => $month,
                'month_name' => $this->getMonthName($month),
            ];
        }

        $activities = $this->getActivitiesByYear($year, $bagId);
        
        // Load expenses for activities when no user_id (all users combined)
        $activityExpenses = [];
        $confirmedExpensesTotals = [];
        if ($userId === null && !empty($activities)) {
            $activityIds = array_column($activities, 'id');
            $activityExpenses = $this->getExpensesByActivityIds($activityIds);
            $confirmedExpensesTotals = $this->getConfirmedExpensesTotalByActivityIds($activityIds);
        }
        
        $grouped = [];
        for ($m = 1; $m <= 12; $m++) {
            $grouped[$m] = [
                'name' => $this->getMonthName($m),
                'weeks' => [],
                'activities' => [],
                'subtotal' => 0,
                'subtotal_paid' => 0,
                'activities_total' => 0,
            ];
        }

        foreach ($weeks as $weekData) {
            $monthKey = $weekData['month'];
            $grouped[$monthKey]['weeks'][] = $weekData;
            $grouped[$monthKey]['subtotal'] += $weekData['value'];
        }
        
        foreach ($activities as $activity) {
            $monthKey = (int)date('n', strtotime($activity['activity_date']));
            $activity['base_value'] = $activity['value'];
            $activity['multiplied_value'] = $activity['value'] * $multiplier;
            
            // Add expenses data (only for all users combined view)
            $actId = $activity['id'];
            $activity['expenses'] = $activityExpenses[$actId] ?? [];
            $activity['confirmed_expenses'] = $confirmedExpensesTotals[$actId] ?? 0;
            $activity['net_value'] = $activity['multiplied_value'] - $activity['confirmed_expenses'];
            
            $activity['paid'] = false;
            $activity['pending'] = $activity['net_value'];
            $activity['partial'] = false;
            $activity['paid_amount'] = 0;
            $grouped[$monthKey]['activities'][] = $activity;
            $grouped[$monthKey]['subtotal'] += $activity['net_value'];
        }

        $remainingPayment = $totalPaid;
        foreach ($grouped as $monthKey => &$monthData) {
            foreach ($monthData['weeks'] as $index => $weekData) {
                $value = $monthData['weeks'][$index]['value'];
                
                if ($remainingPayment >= $value) {
                    $monthData['weeks'][$index]['paid'] = true;
                    $monthData['weeks'][$index]['pending'] = 0;
                    $remainingPayment -= $value;
                    $monthData['subtotal_paid'] += $value;
                } elseif ($remainingPayment > 0) {
                    $monthData['weeks'][$index]['paid'] = false;
                    $monthData['weeks'][$index]['pending'] = $value - $remainingPayment;
                    $monthData['weeks'][$index]['partial'] = true;
                    $monthData['weeks'][$index]['paid_amount'] = $remainingPayment;
                    $monthData['subtotal_paid'] += $remainingPayment;
                    $remainingPayment = 0;
                } else {
                    $monthData['weeks'][$index]['paid'] = false;
                    $monthData['weeks'][$index]['pending'] = $value;
                    $monthData['weeks'][$index]['partial'] = false;
                    $monthData['weeks'][$index]['paid_amount'] = 0;
                }
            }
            
            foreach ($monthData['activities'] as $index => $activity) {
                $value = $monthData['activities'][$index]['multiplied_value'];
                
                if ($remainingPayment >= $value) {
                    $monthData['activities'][$index]['paid'] = true;
                    $monthData['activities'][$index]['pending'] = 0;
                    $remainingPayment -= $value;
                    $monthData['subtotal_paid'] += $value;
                } elseif ($remainingPayment > 0) {
                    $monthData['activities'][$index]['paid'] = false;
                    $monthData['activities'][$index]['pending'] = $value - $remainingPayment;
                    $monthData['activities'][$index]['partial'] = true;
                    $monthData['activities'][$index]['paid_amount'] = $remainingPayment;
                    $monthData['subtotal_paid'] += $remainingPayment;
                    $remainingPayment = 0;
                } else {
                    $monthData['activities'][$index]['paid'] = false;
                    $monthData['activities'][$index]['pending'] = $value;
                    $monthData['activities'][$index]['partial'] = false;
                    $monthData['activities'][$index]['paid_amount'] = 0;
                }
            }
        }

        $totalYearGoal = 52 * 53 / 2 * 1000 * $multiplier;
        $totalActivities = 0;
        $totalConfirmedExpenses = 0;
        foreach ($activities as $activity) {
            $totalActivities += $activity['value'] * $multiplier;
            $totalConfirmedExpenses += ($confirmedExpensesTotals[$activity['id']] ?? 0);
        }
        $totalActivitiesNet = max(0, $totalActivities - $totalConfirmedExpenses);
        $totalYearGoalWithActivities = $totalYearGoal + $totalActivitiesNet;
        $totalPaidAmount = min($totalPaid, $totalYearGoalWithActivities);

        return [
            'year' => $year,
            'user_id' => $userId,
            'multiplier' => $multiplier,
            'users_multipliers' => $usersMultipliers,
            'grouped' => $grouped,
            'total_paid' => $totalPaid,
            'total_year_goal' => $totalYearGoal,
            'total_activities' => $totalActivities,
            'total_confirmed_expenses' => $totalConfirmedExpenses,
            'total_activities_net' => $totalActivitiesNet,
            'total_year_goal_with_activities' => $totalYearGoalWithActivities,
            'total_pending' => max(0, $totalYearGoalWithActivities - $totalPaidAmount),
            'progress_percent' => $totalYearGoalWithActivities > 0 ? round(($totalPaidAmount / $totalYearGoalWithActivities) * 100, 1) : 0,
        ];
    }

    private function getWeekMonth($weekStart, $year)
    {
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        $startMonth = (int)$weekStart->format('n');
        $startYear = (int)$weekStart->format('Y');
        $endMonth = (int)$weekEnd->format('n');
        $endYear = (int)$weekEnd->format('Y');

        if ($startYear < $year && $endYear == $year) {
            return $endMonth;
        }

        if ($startYear == $year) {
            return $startMonth;
        }

        return $startMonth;
    }

    public function getTotalPaid($userId = null, $bagId = null)
    {
        $query = "SELECT COALESCE(SUM(s.amount), 0) as total 
                  FROM savings s
                  JOIN users u ON s.user_id = u.id AND u.status = 1 AND u.deleted_at IS NULL
                  WHERE s.status = 'verified' AND s.is_active = 1 AND s.deleted_at IS NULL
                  AND u.role != 3";
        
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

    public function getCurrentWeekNumber($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $jan1 = new DateTime("$year-01-01");
        $dayOfWeek = (int)$jan1->format('w');
        if ($dayOfWeek == 0) {
            $dayOfWeek = 7;
        }
        
        $week1Start = clone $jan1;
        $week1Start->modify('-' . ($dayOfWeek - 1) . ' days');

        $now = new DateTime();
        $diff = $now->diff($week1Start)->days;
        
        if ($now < $week1Start) {
            return 0;
        }

        return min(52, intdiv($diff, 7) + 1);
    }

    private function getActivitiesByYear($year, $bagId = null)
    {
        $query = "SELECT * FROM activities 
                  WHERE YEAR(activity_date) = :year AND is_active = 1 AND deleted_at IS NULL";
        
        $params = [':year' => $year];
        
        if ($bagId !== null) {
            $query .= " AND bag_id = :bag_id";
            $params[':bag_id'] = $bagId;
        }
        
        $query .= " ORDER BY activity_date ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExpensesByActivityIds($activityIds)
    {
        if (empty($activityIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
        $query = "SELECT * FROM expenses 
                  WHERE activity_id IN ($placeholders) AND is_active = 1 AND deleted_at IS NULL
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($activityIds);
        
        $expenses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $expenses[$row['activity_id']][] = $row;
        }
        return $expenses;
    }

    public function getConfirmedExpensesTotalByActivityIds($activityIds)
    {
        if (empty($activityIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
        $query = "SELECT activity_id, COALESCE(SUM(amount), 0) as total 
                  FROM expenses 
                  WHERE activity_id IN ($placeholders) AND status = 'confirmed' AND is_active = 1 AND deleted_at IS NULL
                  GROUP BY activity_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($activityIds);
        
        $totals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totals[$row['activity_id']] = $row['total'];
        }
        return $totals;
    }
}
