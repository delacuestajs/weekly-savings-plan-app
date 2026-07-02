<?php require_once __DIR__ . '/../views/header.php'; ?>

<?php
// Weekly plan summary variables
$weeklyYearGoal = $weeklyData['year_goal'];
$weeklyTotalPaid = $weeklyData['total_paid'];
$weeklyPending = $weeklyData['pending'];
$weeklyProgressPercent = $weeklyData['percent'];
$currentWeek = $weeklyData['current_week'];
$paidWeeks = $weeklyData['paid_weeks'];
$partialWeeks = $weeklyData['partial_weeks'];
$pendingWeeks = $weeklyData['pending_weeks'];
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <!-- Weekly Plan Summary -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase"><?= Locale::get('weekly_plan') ?></h3>
                <span class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-800">$<?= number_format($weeklyTotalPaid, 0) ?></p>
            <p class="text-sm text-gray-500 mb-2"><?= $weeklyProgressPercent ?>% <?= Locale::get('of_goal') ?></p>
            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: <?= min(100, $weeklyProgressPercent) ?>%"></div>
            </div>
            <div class="text-sm text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span><?= Locale::get('year_goal') ?>:</span>
                    <span class="font-medium">$<?= number_format($weeklyYearGoal, 0) ?></span>
                </div>
                <div class="flex justify-between">
                    <span><?= Locale::get('remaining') ?>:</span>
                    <span class="font-medium text-orange-600">$<?= number_format($weeklyPending, 0) ?></span>
                </div>
                <div class="flex justify-between pt-1 mt-1 border-t border-gray-100">
                    <span><?= Locale::get('current_week') ?>:</span>
                    <span class="font-medium"><?= $currentWeek ?>/52</span>
                </div>
                <div class="flex justify-between">
                    <span><?= Locale::get('paid') ?>:</span>
                    <span class="font-medium text-green-600"><?= $paidWeeks ?></span>
                </div>
                <?php if ($partialWeeks > 0): ?>
                <div class="flex justify-between">
                    <span><?= Locale::get('partial') ?>:</span>
                    <span class="font-medium text-yellow-600"><?= $partialWeeks ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <span><?= Locale::get('pending') ?>:</span>
                    <span class="font-medium text-red-600"><?= $pendingWeeks ?></span>
                </div>
            </div>
        </div>
        <a href="index.php?action=weekly" class="block bg-gray-50 px-3 md:px-4 py-2 text-sm text-teal-500 hover:underline font-medium border-t border-gray-100"><?= Locale::get('view') ?> &rarr;</a>
    </div>

    <!-- Payments Summary -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase"><?= Locale::get('payments') ?></h3>
                <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-800">$<?= number_format($totalPayments, 0) ?></p>
            <p class="text-sm text-gray-500"><?= $paymentsCount ?> <?= Locale::get('payments') ?></p>
            <div class="hidden lg:block mt-3 pt-3 border-t border-gray-100">
                <div class="space-y-2">
                    <?php foreach (array_slice($recentPayments, 0, 5) as $p): ?>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-[9px] flex-shrink-0">
                                <?= strtoupper(substr($p['firstname'] ?? '', 0, 1) . substr($p['lastname'] ?? '', 0, 1)) ?>
                            </div>
                            <span class="text-gray-700 truncate"><?= htmlspecialchars(($p['firstname'] ?? '') . ' ' . ($p['lastname'] ?? '')) ?></span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="font-medium text-gray-800">$<?= number_format($p['amount'], 0) ?></span>
                            <?php
                            $statusClass = ($p['status'] ?? '') === 'verified' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700';
                            ?>
                            <span class="inline-block px-1.5 py-0.5 text-[10px] rounded-full <?= $statusClass ?>"><?= Locale::get($p['status'] ?? 'unverified') ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <a href="index.php?action=payments" class="block bg-gray-50 px-3 md:px-4 py-2 text-sm text-blue-500 hover:underline font-medium border-t border-gray-100"><?= Locale::get('view') ?> &rarr;</a>
    </div>

    <!-- Users Summary -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase"><?= Locale::get('users') ?></h3>
                <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-800"><?= $usersCount ?></p>
            <p class="text-sm text-gray-500"><?= $activeUsers ?> <?= strtolower(Locale::get('role_normal')) ?></p>
            <div class="hidden lg:block mt-3 pt-3 border-t border-gray-100">
                <div class="space-y-2">
                    <?php foreach ($recentUsers as $u): ?>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-[9px] flex-shrink-0">
                                <?= strtoupper(substr($u['firstname'], 0, 1) . substr($u['lastname'], 0, 1)) ?>
                            </div>
                            <span class="text-gray-700 truncate"><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></span>
                        </div>
                        <?php
                        $role = $u['role'] ?? 1;
                        $roleClass = $role == 2 ? 'bg-purple-100 text-purple-700' : ($role == 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700');
                        $roleLabel = $role == 2 ? Locale::get('role_admin') : ($role == 0 ? Locale::get('role_disabled') : Locale::get('role_normal'));
                        ?>
                        <span class="inline-block px-1.5 py-0.5 text-[10px] rounded-full <?= $roleClass ?>"><?= $roleLabel ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <a href="index.php?module=user" class="block bg-gray-50 px-3 md:px-4 py-2 text-sm text-purple-500 hover:underline font-medium border-t border-gray-100"><?= Locale::get('view') ?> &rarr;</a>
    </div>

    <!-- Activities Summary -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase"><?= Locale::get('activities') ?></h3>
                <span class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-800">$<?= number_format($activitiesTotal, 0) ?></p>
            <p class="text-sm text-gray-500"><?= $activitiesCount ?> <?= Locale::get('activities') ?></p>
        </div>
        <a href="index.php?module=activity" class="block bg-gray-50 px-3 md:px-4 py-2 text-sm text-orange-500 hover:underline font-medium border-t border-gray-100"><?= Locale::get('view') ?> &rarr;</a>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
