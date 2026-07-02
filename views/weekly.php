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

<!-- Dashboard Layout -->
<div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
    
    <!-- Left Panel: Stats & Filters (Desktop) -->
    <div class="hidden lg:block lg:w-64 xl:w-72 flex-shrink-0">
        <div class="sticky top-20 space-y-4">
            <!-- Year Navigation -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center justify-between mb-3">
                    <a href="index.php?action=weekly&year=<?= $data['year'] - 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="p-1 hover:bg-gray-100 rounded transition">&larr;</a>
                    <span class="text-lg font-bold text-gray-800"><?= $data['year'] ?></span>
                    <a href="index.php?action=weekly&year=<?= $data['year'] + 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="p-1 hover:bg-gray-100 rounded transition">&rarr;</a>
                </div>
                <?php if (Auth::isAdmin()): ?>
                <select id="user_filter_desktop" onchange="filterByUser(this)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_users_combined') ?></option>
                    <?php if (!empty($usersList)): ?>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (isset($data['user_id']) && $data['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?> (x<?= $u['multiplier'] ?? 1 ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php endif; ?>
            </div>

            <!-- Summary Stats -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3"><?= Locale::get('summary') ?? 'Summary' ?></h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('year_goal') ?></span>
                        <span class="text-sm font-bold text-blue-600">$<?= number_format($data['total_year_goal'], 0) ?></span>
                    </div>
                    <?php if (!empty($data['user_id']) && $data['total_activities'] > 0): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('activities') ?></span>
                        <span class="text-sm font-bold text-orange-600">$<?= number_format($data['total_activities'], 0) ?></span>
                    </div>
                    <?php elseif (empty($data['user_id']) && $data['total_activities'] > 0): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('activities') ?></span>
                        <span class="text-sm font-bold text-gray-400 line-through">$<?= number_format($data['total_activities'], 0) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('activities') ?> (<?= Locale::get('net') ?>)</span>
                        <span class="text-sm font-bold text-orange-600">$<?= number_format($data['total_activities_net'], 0) ?></span>
                    </div>
                    <?php if ($data['total_confirmed_expenses'] > 0): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400 ml-2"><?= Locale::get('expenses') ?></span>
                        <span class="text-xs font-medium text-red-500">-$<?= number_format($data['total_confirmed_expenses'], 0) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('total_paid') ?></span>
                        <span class="text-sm font-bold text-green-600">$<?= number_format($data['total_paid'], 0) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600"><?= Locale::get('remaining') ?></span>
                        <span class="text-sm font-bold text-orange-600">$<?= number_format($data['total_pending'], 0) ?></span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500"><?= $data['progress_percent'] ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: <?= $data['progress_percent'] ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Multiplier Info -->
            <?php if (!empty($data['user_id']) && $data['multiplier'] > 1): ?>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center gap-2 text-sm text-purple-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <span><?= Locale::get('multiplier') ?>: <strong>&times;<?= $data['multiplier'] ?></strong></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Users List (Combined View) -->
            <?php if (empty($data['user_id']) && !empty($data['users_multipliers'])): ?>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3"><?= Locale::get('users_multipliers') ?></h3>
                <div class="space-y-2">
                    <?php foreach ($data['users_multipliers'] as $u): ?>
                        <?php $m = max(1, (int)$u['multiplier']); ?>
                        <a href="index.php?action=weekly&year=<?= $data['year'] ?>&user_id=<?= $u['id'] ?>" class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition">
                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></span>
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">&times;<?= $m ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3"><?= Locale::get('actions') ?></h3>
                <div class="space-y-2">
                    <button onclick="openCreatePaymentModal()" class="w-full flex items-center gap-2 p-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <?= Locale::get('add_new_saving') ?>
                    </button>
                    <a href="index.php?action=payments" class="flex items-center gap-2 p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <?= Locale::get('payments') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Center: Week Grid -->
    <div class="flex-1 min-w-0">
        <!-- Mobile Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4 lg:hidden">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-xl font-bold text-gray-800"><?= Locale::get('weekly_savings_plan') ?> <?= $data['year'] ?></h1>
                <div class="flex items-center gap-1">
                    <a href="index.php?action=weekly&year=<?= $data['year'] - 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="p-2 hover:bg-gray-100 rounded-lg transition">&larr;</a>
                    <a href="index.php?action=weekly&year=<?= $data['year'] + 1 ?>&user_id=<?= $data['user_id'] ?? '' ?>" class="p-2 hover:bg-gray-100 rounded-lg transition">&rarr;</a>
                </div>
            </div>
            <?php if (Auth::isAdmin()): ?>
            <select id="user_filter_mobile" onchange="filterByUser(this)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value=""><?= Locale::get('all_users_combined') ?></option>
                <?php if (!empty($usersList)): ?>
                    <?php foreach ($usersList as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (isset($data['user_id']) && $data['user_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?> (x<?= $u['multiplier'] ?? 1 ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php endif; ?>
            
            <!-- Mobile Stats -->
            <div class="grid grid-cols-2 gap-2 mt-3">
                <div class="bg-blue-50 rounded-lg p-2 text-center">
                    <p class="text-xs text-blue-600"><?= Locale::get('year_goal') ?></p>
                    <p class="text-sm font-bold text-blue-800">$<?= number_format($data['total_year_goal'], 0) ?></p>
                </div>
                <?php if ($data['total_activities'] > 0): ?>
                <div class="bg-orange-50 rounded-lg p-2 text-center">
                    <p class="text-xs text-orange-600"><?= Locale::get('activities') ?> (<?= Locale::get('net') ?>)</p>
                    <p class="text-sm font-bold text-orange-800">$<?= number_format($data['total_activities_net'], 0) ?></p>
                </div>
                <?php endif; ?>
                <div class="bg-green-50 rounded-lg p-2 text-center">
                    <p class="text-xs text-green-600"><?= Locale::get('total_paid') ?></p>
                    <p class="text-sm font-bold text-green-800">$<?= number_format($data['total_paid'], 0) ?></p>
                </div>
                <div class="bg-red-50 rounded-lg p-2 text-center">
                    <p class="text-xs text-red-600"><?= Locale::get('remaining') ?></p>
                    <p class="text-sm font-bold text-red-800">$<?= number_format($data['total_pending'], 0) ?></p>
                </div>
            </div>
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-1.5 rounded-full" style="width: <?= $data['progress_percent'] ?>%"></div>
                </div>
            </div>

            <!-- How it works (Mobile) -->
            <div class="mt-3 lg:hidden px-1">
                <button onclick="toggleHowItWorks()" class="flex items-center justify-between w-full text-sm font-medium text-gray-500 hover:text-gray-700">
                    <span><?= Locale::get('how_it_works') ?></span>
                    <svg id="howItWorksIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="howItWorksContent" class="hidden mt-2 text-sm text-gray-500 space-y-1.5">
                    <p><?= Locale::get('week_target_formula') ?></p>
                    <p><?= Locale::get('multiplier_explanation') ?></p>
                    <p><?= Locale::get('status_legend') ?></p>
                </div>
            </div>
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
            <div class="bg-white rounded-lg shadow-sm mb-3 <?= $isCurrentMonth ? 'ring-2 ring-blue-400' : '' ?>">
                <div class="p-3 md:p-4 cursor-pointer flex flex-wrap items-center justify-between gap-2" onclick="toggleMonth('month-<?= $monthNum ?>')">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base md:text-lg font-bold text-gray-800"><?= $monthData['name'] ?></h2>
                        <?php if ($isCurrentMonth): ?>
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded"><?= Locale::get('current') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($monthData['activities'])): ?>
                            <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-xs font-medium rounded">+<?= count($monthData['activities']) ?> <?= Locale::get('activities') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-green-600 font-medium">$<?= number_format($monthPaid, 0) ?></span>
                        <span class="text-gray-400">/</span>
                        <span class="text-orange-600 font-medium">$<?= number_format($monthPending, 0) ?></span>
                        <svg id="month-<?= $monthNum ?>-icon" class="w-4 h-4 text-gray-400 transition-transform <?= $isCurrentMonth ? '' : 'rotate-[-90deg]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div id="month-<?= $monthNum ?>" class="<?= $isCurrentMonth ? '' : 'hidden' ?>">
                    <div class="px-3 md:px-4 pb-2">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $monthPercent ?>%"></div>
                        </div>
                    </div>

                    <div class="px-3 md:px-4 pb-3">
                    <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                    <?php foreach ($monthData['weeks'] as $weekData): ?>
                        <?php
                        $isCurrentWeek = ($weekData['week'] == $currentWeek && $data['year'] == $currentYear);
                        $bgClass = '';
                        $borderClass = '';
                        $textClass = '';

                        if ($weekData['paid']) {
                            $bgClass = 'bg-green-50';
                            $borderClass = 'border-green-200';
                            $textClass = 'text-green-700';
                        } elseif (!empty($weekData['partial'])) {
                            $bgClass = 'bg-yellow-50';
                            $borderClass = 'border-yellow-200';
                            $textClass = 'text-yellow-700';
                        } else {
                            $bgClass = 'bg-red-50';
                            $borderClass = 'border-red-100';
                            $textClass = 'text-red-700';
                        }

                        if ($isCurrentWeek) {
                            $borderClass = 'border-blue-400 ring-1 ring-blue-300';
                        }
                        ?>
                        <div class="<?= $bgClass ?> border <?= $borderClass ?> rounded-lg p-2 transition-all hover:shadow-sm cursor-default flex flex-col">
                            <div class="text-center mb-1">
                                <span class="text-sm font-bold <?= $textClass ?>"><?= Locale::get('week') ?> <?= $weekData['week'] ?></span>
                            </div>
                            <div class="text-[11px] text-gray-400 text-center mb-auto">
                                <?= translatedDate($weekData['start_date']) ?> - <?= translatedDate($weekData['end_date']) ?>
                            </div>
                            <div class="flex justify-end items-center mt-1">
                                <span class="text-sm font-bold <?= $textClass ?>">
                                    $<?= number_format($weekData['value'], 0) ?>
                                </span>
                            </div>
                            <?php if (!$weekData['paid']): ?>
                                <div class="flex justify-end">
                                    <span class="text-xs text-gray-400">
                                        -$<?= number_format($weekData['pending'], 0) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($weekData['partial'])): ?>
                                <div class="mt-1.5">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-yellow-400 h-1.5 rounded-full" style="width: <?= round((($weekData['paid_amount'] ?? 0) / $weekData['value']) * 100) ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    </div>
                
                <?php if (!empty($monthData['activities'])): ?>
                    <div class="px-3 md:px-4 pb-3 pt-3 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-500 mb-2"><?= Locale::get('activities') ?></h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
                        <?php foreach ($monthData['activities'] as $activity): ?>
                            <?php
                            $actPaid = $activity['paid'] ?? false;
                            $actPartial = $activity['partial'] ?? false;
                            $actPending = $activity['pending'] ?? 0;
                            $actPaidAmount = $activity['paid_amount'] ?? 0;
                            $actMultipliedValue = $activity['multiplied_value'] ?? $activity['value'] ?? 0;
                            $actNetValue = $activity['net_value'] ?? $actMultipliedValue;
                            $actExpenses = $activity['expenses'] ?? [];
                            $actConfirmedExpenses = $activity['confirmed_expenses'] ?? 0;
                            $showExpenses = empty($data['user_id']) && !empty($actExpenses);
                            
                            $actBgClass = '';
                            $actBorderClass = '';
                            $actTextClass = '';

                            if ($actPaid) {
                                $actBgClass = 'bg-green-50';
                                $actBorderClass = 'border-green-200';
                                $actTextClass = 'text-green-700';
                            } elseif ($actPartial) {
                                $actBgClass = 'bg-yellow-50';
                                $actBorderClass = 'border-yellow-200';
                                $actTextClass = 'text-yellow-700';
                            } else {
                                $actBgClass = 'bg-red-50';
                                $actBorderClass = 'border-red-100';
                                $actTextClass = 'text-red-700';
                            }
                            ?>
                            <div class="<?= $actBgClass ?> border <?= $actBorderClass ?> rounded-lg p-2 transition-all hover:shadow-sm cursor-default flex flex-col">
                                <div class="text-center mb-1">
                                    <span class="text-sm font-bold <?= $actTextClass ?> truncate"><?= htmlspecialchars($activity['name']) ?></span>
                                </div>
                                <div class="text-[11px] text-gray-400 text-center mb-auto">
                                    <?= date('M d', strtotime($activity['activity_date'])) ?>
                                </div>
                                
                                <?php if ($showExpenses): ?>
                                <!-- Confirmed expenses only -->
                                <?php 
                                $confirmedExpenses = array_filter($actExpenses, function($e) { return $e['status'] === 'confirmed'; });
                                ?>
                                <?php if (!empty($confirmedExpenses)): ?>
                                <div class="mt-1 mb-1 border-t border-gray-200 pt-1">
                                    <?php foreach ($confirmedExpenses as $expense): ?>
                                    <div class="flex justify-between items-center text-[10px] text-red-600">
                                        <span class="truncate mr-1"><?= htmlspecialchars($expense['description']) ?></span>
                                        <span class="flex-shrink-0">-$<?= number_format($expense['amount'], 0) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="flex justify-end items-center mt-1">
                                    <span class="text-sm font-bold <?= $actTextClass ?>">
                                        $<?= number_format($actNetValue, 0) ?>
                                    </span>
                                </div>
                                <?php if ($actConfirmedExpenses > 0): ?>
                                    <div class="flex justify-end">
                                        <span class="text-[10px] text-red-500">
                                            <?= Locale::get('expenses') ?>: -$<?= number_format($actConfirmedExpenses, 0) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!$actPaid): ?>
                                    <div class="flex justify-end">
                                        <span class="text-xs text-gray-400">
                                            -$<?= number_format($actPending, 0) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($actPartial): ?>
                                    <div class="mt-1.5">
                                        <div class="w-full bg-gray-200 rounded-full h-1">
                                            <div class="bg-yellow-400 h-1 rounded-full" style="width: <?= $actNetValue > 0 ? round(($actPaidAmount / $actNetValue) * 100) : 0 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Right Panel: Activity Log (Desktop) -->
    <div class="hidden lg:block lg:w-64 xl:w-72 flex-shrink-0">
        <div class="sticky top-20 space-y-4">
            <!-- How it works -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3"><?= Locale::get('how_it_works') ?></h3>
                <ul class="text-xs text-gray-600 space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><?= Locale::get('week_target_formula') ?></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><?= Locale::get('multiplier_explanation') ?></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><?= Locale::get('status_legend') ?></span>
                    </li>
                </ul>
            </div>

            <!-- Recent Activity -->
            <?php if (Auth::isAdmin()): ?>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3"><?= Locale::get('activity_logs') ?></h3>
                <a href="index.php?module=log" class="text-xs text-blue-500 hover:underline"><?= Locale::get('view') ?> &rarr;</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
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

function toggleMonth(id) {
    var content = document.getElementById(id);
    var icon = document.getElementById(id + '-icon');
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('rotate-[-90deg]');
    } else {
        content.classList.add('hidden');
        icon.classList.add('rotate-[-90deg]');
    }
}

function toggleHowItWorks() {
    var content = document.getElementById('howItWorksContent');
    var icon = document.getElementById('howItWorksIcon');
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
</script>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
