<?php require_once __DIR__ . '/../views/header.php'; ?>

<?php
function translatedDate($dateStr) {
    $ts = strtotime($dateStr);
    $month = (int)date('n', $ts);
    $day = date('d', $ts);
    $shortMonths = [
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
    return ($shortMonths[$month] ?? date('M', $ts)) . ' ' . $day;
}
?>

<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?= Locale::get('weekly_savings_plan') ?> <?= $data['year'] ?></h1>
            <div class="flex items-center gap-2">
                <a href="index.php?action=weekly&year=<?= $data['year'] - 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition text-sm">&larr; <?= $data['year'] - 1 ?></a>
                <span class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium"><?= $data['year'] ?></span>
                <a href="index.php?action=weekly&year=<?= $data['year'] + 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition text-sm"><?= $data['year'] + 1 ?> &rarr;</a>
            </div>
        </div>

        <div class="mb-6">
            <label for="user_filter" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('view_savings_for') ?></label>
            <div class="flex items-center gap-3">
                <select id="user_filter" onchange="filterByUser(this)" class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value=""><?= Locale::get('all_users_combined') ?></option>
                    <?php if (!empty($usersList)): ?>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (isset($data['user_id']) && $data['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?> (x<?= $u['multiplier'] ?? 1 ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (!empty($data['user_id'])): ?>
                    <a href="index.php?module=user&action=edit&id=<?= $data['user_id'] ?>" class="px-3 py-2 bg-amber-400 hover:bg-amber-500 text-black rounded-lg transition text-sm font-medium"><?= Locale::get('edit_user') ?></a>
                    <a href="index.php?action=weekly&year=<?= $data['year'] ?>" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition text-sm"><?= Locale::get('clear') ?></a>
                <?php endif; ?>
            </div>
            <?php if (!empty($data['user_id']) && $data['multiplier'] > 1): ?>
                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <?= Locale::get('multiplier') ?>: <strong>&times;<?= $data['multiplier'] ?></strong> - <?= Locale::get('weekly_goals_multiplied') ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
        function filterByUser(select) {
            var userId = select.value;
            var year = <?= $data['year'] ?>;
            var url = 'index.php?action=weekly&year=' + year;
            if (userId) {
                url += '&user_id=' + userId;
            }
            window.location.href = url;
        }
        </script>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-600 font-medium"><?= Locale::get('year_goal') ?></p>
                <p class="text-2xl font-bold text-blue-800">$<?= number_format($data['total_year_goal'], 2) ?></p>
                <p class="text-xs text-blue-500">
                    <?php if (empty($data['user_id']) && !empty($data['users_multipliers'])): ?>
                        <?= Locale::get('combined') ?>: <?php 
                        $parts = [];
                        foreach ($data['users_multipliers'] as $u) {
                            $m = max(1, (int)$u['multiplier']);
                            $parts[] = htmlspecialchars($u['firstname']) . ' (x' . $m . ')';
                        }
                        echo implode(' + ', $parts);
                        ?>
                    <?php elseif ($data['multiplier'] > 1): ?>
                        52 <?= Locale::get('week') ?>s &times; week# &times; $1,000 &times; <?= $data['multiplier'] ?>
                    <?php else: ?>
                        52 <?= Locale::get('week') ?>s &times; week number &times; $1,000
                    <?php endif; ?>
                </p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium"><?= Locale::get('total_paid') ?></p>
                <p class="text-2xl font-bold text-green-800">$<?= number_format($data['total_paid'], 2) ?></p>
                <p class="text-xs text-green-500"><?= $data['progress_percent'] ?>% <?= Locale::get('of_goal') ?></p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <p class="text-sm text-orange-600 font-medium"><?= Locale::get('remaining') ?></p>
                <p class="text-2xl font-bold text-orange-800">$<?= number_format($data['total_pending'], 2) ?></p>
                <p class="text-xs text-orange-500"><?= Locale::get('still_to_pay') ?></p>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-gray-700"><?= Locale::get('overall_progress') ?></span>
                <span class="font-medium text-gray-700"><?= $data['progress_percent'] ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-4 rounded-full transition-all duration-500" style="width: <?= $data['progress_percent'] ?>%"></div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-gray-700 mb-2"><?= Locale::get('how_it_works') ?></h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li><?= Locale::get('week_target_formula') ?></li>
                <li><?= Locale::get('week_values_example') ?></li>
                <li><?= Locale::get('multiplier_explanation') ?></li>
                <li><?= Locale::get('payments_applied') ?></li>
                <li><?= Locale::get('status_legend') ?></li>
            </ul>
        </div>

        <?php if (empty($data['user_id']) && !empty($data['users_multipliers'])): ?>
        <div class="bg-purple-50 rounded-lg p-4">
            <h3 class="font-semibold text-purple-700 mb-2"><?= Locale::get('users_multipliers') ?>:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                <?php foreach ($data['users_multipliers'] as $u): ?>
                    <?php $m = max(1, (int)$u['multiplier']); ?>
                    <a href="index.php?action=weekly&year=<?= $data['year'] ?>&user_id=<?= $u['id'] ?>" class="flex items-center gap-2 p-2 bg-white rounded-lg hover:bg-purple-100 transition">
                        <span class="font-medium text-purple-800"><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></span>
                        <span class="px-2 py-0.5 bg-purple-200 text-purple-800 text-xs rounded-full">&times;<?= $m ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php 
    $weeklyModel = new WeeklySaving();
    $currentWeek = $weeklyModel->getCurrentWeekNumber($data['year']);
    $currentYear = date('Y');
    ?>

    <?php foreach ($data['grouped'] as $monthNum => $monthData): ?>
        <?php
        $monthPaid = $monthData['subtotal_paid'];
        $monthTotal = $monthData['subtotal'];
        $monthPending = $monthTotal - $monthPaid;
        $monthPercent = $monthTotal > 0 ? round(($monthPaid / $monthTotal) * 100, 1) : 0;
        $isCurrentMonth = ($monthNum == date('n') && $data['year'] == $currentYear);
        ?>
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-4 <?= $isCurrentMonth ? 'ring-2 ring-blue-500' : '' ?>">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800"><?= $monthData['name'] ?></h2>
                    <?php if ($isCurrentMonth): ?>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full"><?= Locale::get('current') ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-green-600 font-medium"><?= Locale::get('paid') ?>: $<?= number_format($monthPaid, 2) ?></span>
                    <span class="text-orange-600 font-medium"><?= Locale::get('pending') ?>: $<?= number_format($monthPending, 2) ?></span>
                </div>
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-500"><?= $monthPercent ?>% <?= strtolower(Locale::get('paid')) ?></span>
                    <span class="text-gray-500">$<?= number_format($monthPaid, 2) ?> / $<?= number_format($monthTotal, 2) ?></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: <?= $monthPercent ?>%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                <?php foreach ($monthData['weeks'] as $weekData): ?>
                    <?php
                    $isCurrentWeek = ($weekData['week'] == $currentWeek && $data['year'] == $currentYear);
                    $bgClass = '';
                    $borderClass = '';
                    $textClass = '';
                    $statusIcon = '';
                    $statusText = '';

                    if ($weekData['paid']) {
                        $bgClass = 'bg-green-50';
                        $borderClass = 'border-green-300';
                        $textClass = 'text-green-800';
                        $statusIcon = '✓';
                        $statusText = Locale::get('paid');
                    } elseif (!empty($weekData['partial'])) {
                        $bgClass = 'bg-yellow-50';
                        $borderClass = 'border-yellow-300';
                        $textClass = 'text-yellow-800';
                        $statusIcon = '⏳';
                        $statusText = Locale::get('partial');
                    } else {
                        $bgClass = 'bg-red-50';
                        $borderClass = 'border-red-200';
                        $textClass = 'text-red-800';
                        $statusIcon = '✗';
                        $statusText = Locale::get('unpaid');
                    }

                    if ($isCurrentWeek) {
                        $borderClass = 'border-blue-500 ring-2 ring-blue-300';
                    }
                    ?>
                    <div class="<?= $bgClass ?> border <?= $borderClass ?> rounded-lg p-3 transition-all hover:shadow-md">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-bold <?= $textClass ?>"><?= Locale::get('week') ?> <?= $weekData['week'] ?></span>
                                <?php if ($isCurrentWeek): ?>
                                    <span class="ml-1 px-1 py-0.5 bg-blue-200 text-blue-800 text-xs rounded"><?= Locale::get('now') ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="text-lg"><?= $statusIcon ?></span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            <?= translatedDate($weekData['start_date']) ?> - <?= translatedDate($weekData['end_date']) ?>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium <?= $textClass ?>">
                                $<?= number_format($weekData['value'], 0) ?>
                            </span>
                            <?php if (!$weekData['paid']): ?>
                                <span class="text-xs text-gray-500">
                                    <?= Locale::get('pending') ?>: $<?= number_format($weekData['pending'], 0) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($weekData['partial'])): ?>
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?= round(($weekData['paid_amount'] / $weekData['value']) * 100) ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($monthData['activities'])): ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600 mb-3"><?= Locale::get('activities') ?></h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    <?php foreach ($monthData['activities'] as $activity): ?>
                        <?php
                        $actPaid = $activity['paid'] ?? false;
                        $actPartial = $activity['partial'] ?? false;
                        $actPending = $activity['pending'] ?? 0;
                        $actPaidAmount = $activity['paid_amount'] ?? 0;
                        $actMultipliedValue = $activity['multiplied_value'] ?? $activity['value'] ?? 0;
                        
                        $actBgClass = '';
                        $actBorderClass = '';
                        $actTextClass = '';
                        $actStatusIcon = '';
                        $actStatusText = '';

                        if ($actPaid) {
                            $actBgClass = 'bg-green-50';
                            $actBorderClass = 'border-green-300';
                            $actTextClass = 'text-green-800';
                            $actStatusIcon = '✓';
                            $actStatusText = Locale::get('paid');
                        } elseif ($actPartial) {
                            $actBgClass = 'bg-yellow-50';
                            $actBorderClass = 'border-yellow-300';
                            $actTextClass = 'text-yellow-800';
                            $actStatusIcon = '⏳';
                            $actStatusText = Locale::get('partial');
                        } else {
                            $actBgClass = 'bg-red-50';
                            $actBorderClass = 'border-red-200';
                            $actTextClass = 'text-red-800';
                            $actStatusIcon = '✗';
                            $actStatusText = Locale::get('unpaid');
                        }
                        ?>
                        <div class="<?= $actBgClass ?> border <?= $actBorderClass ?> rounded-lg p-3 transition-all hover:shadow-md">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-bold <?= $actTextClass ?>"><?= htmlspecialchars($activity['name']) ?></span>
                                </div>
                                <span class="text-lg"><?= $actStatusIcon ?></span>
                            </div>
                            <div class="text-xs text-gray-500 mb-2">
                                <?= date('M d, Y', strtotime($activity['activity_date'])) ?>
                            </div>
                            <?php if (!empty($activity['description'])): ?>
                                <p class="text-xs text-gray-600 mb-2"><?= htmlspecialchars($activity['description']) ?></p>
                            <?php endif; ?>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium <?= $actTextClass ?>">
                                    $<?= number_format($actMultipliedValue, 0) ?>
                                </span>
                                <?php if (!$actPaid): ?>
                                    <span class="text-xs text-gray-500">
                                        <?= Locale::get('pending') ?>: $<?= number_format($actPending, 0) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($actPartial): ?>
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?= $actMultipliedValue > 0 ? round(($actPaidAmount / $actMultipliedValue) * 100) : 0 ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
