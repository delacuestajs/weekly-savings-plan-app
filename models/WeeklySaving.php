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

    public function getWeeklyOverview($year = null, $userId = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $multiplier = 1;
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
            $usersQuery = "SELECT id, firstname, lastname, multiplier FROM users WHERE status = 1 AND deleted_at IS NULL";
            $usersStmt = $this->conn->prepare($usersQuery);
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

        $totalPaid = $this->getTotalPaid($userId);
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

        $grouped = [];
        for ($m = 1; $m <= 12; $m++) {
            $grouped[$m] = [
                'name' => $this->getMonthName($m),
                'weeks' => [],
                'subtotal' => 0,
                'subtotal_paid' => 0,
            ];
        }

        foreach ($weeks as $weekData) {
            $monthKey = $weekData['month'];
            $grouped[$monthKey]['weeks'][] = $weekData;
            $grouped[$monthKey]['subtotal'] += $weekData['value'];
        }

        $remainingPayment = $totalPaid;
        foreach ($grouped as $monthKey => &$monthData) {
            foreach ($monthData['weeks'] as &$weekData) {
                if ($remainingPayment >= $weekData['value']) {
                    $weekData['paid'] = true;
                    $weekData['pending'] = 0;
                    $remainingPayment -= $weekData['value'];
                    $monthData['subtotal_paid'] += $weekData['value'];
                } elseif ($remainingPayment > 0) {
                    $weekData['paid'] = false;
                    $weekData['pending'] = $weekData['value'] - $remainingPayment;
                    $weekData['partial'] = true;
                    $weekData['paid_amount'] = $remainingPayment;
                    $monthData['subtotal_paid'] += $remainingPayment;
                    $remainingPayment = 0;
                } else {
                    $weekData['paid'] = false;
                    $weekData['pending'] = $weekData['value'];
                    $weekData['partial'] = false;
                    $weekData['paid_amount'] = 0;
                }
            }
        }

        $totalYearGoal = 52 * 53 / 2 * 1000 * $multiplier;
        $totalPaidAmount = min($totalPaid, $totalYearGoal);

        return [
            'year' => $year,
            'user_id' => $userId,
            'multiplier' => $multiplier,
            'users_multipliers' => $usersMultipliers,
            'grouped' => $grouped,
            'total_paid' => $totalPaid,
            'total_year_goal' => $totalYearGoal,
            'total_pending' => max(0, $totalYearGoal - $totalPaidAmount),
            'progress_percent' => $totalYearGoal > 0 ? round(($totalPaidAmount / $totalYearGoal) * 100, 1) : 0,
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

    public function getTotalPaid($userId = null)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM savings 
                  WHERE status = 'completed' AND is_active = 1 AND deleted_at IS NULL";
        
        if ($userId !== null) {
            $query .= " AND user_id = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($userId !== null) {
            $stmt->bindParam(':user_id', $userId);
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
}
