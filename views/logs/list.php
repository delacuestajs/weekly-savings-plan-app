<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= Locale::get('activity_logs') ?></h1>
    </div>
    
    <form method="GET" class="mb-4">
        <input type="hidden" name="module" value="log">
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('date_from') ?></label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('date_to') ?></label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('action_by') ?></label>
                <select name="user_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_users') ?></option>
                    <option value="0" <?= ($filters['user_id'] ?? '') === '0' ? 'selected' : '' ?>><?= Locale::get('system') ?></option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($filters['user_id'] ?? '') == $user['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('action') ?></label>
                <select name="action" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_actions') ?></option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= ($filters['action'] ?? '') == $act ? 'selected' : '' ?>>
                            <?= Locale::get('log_' . $act, $act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-3 rounded-lg transition text-sm">
                <?= Locale::get('filter') ?>
            </button>
            <a href="index.php?module=log" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition text-sm">
                <?= Locale::get('clear_filters') ?>
            </a>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php if ($logs->rowCount() === 0): ?>
            <div class="text-center py-8 text-gray-500 col-span-full">
                <?= Locale::get('no_logs_found') ?>
            </div>
        <?php else: ?>
            <?php while ($row = $logs->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition flex flex-col">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs text-gray-400"><?= date('M d, H:i:s', strtotime($row['created_at'])) ?></span>
                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded-full <?= getActionBadgeClass($row['action']) ?>">
                                <?= Locale::get('log_' . $row['action'], $row['action']) ?>
                            </span>
                        </div>
                        <?php if ($row['user_fullname']): ?>
                            <div class="flex items-center gap-1">
                                <span class="text-xs font-medium"><?= htmlspecialchars($row['user_fullname']) ?></span>
                                <?php
                                $userRole = $row['user_role'] ?? 1;
                                $roleBadgeClasses = [
                                    0 => 'bg-red-100 text-red-700',
                                    1 => 'bg-green-100 text-green-700',
                                    2 => 'bg-purple-100 text-purple-700'
                                ];
                                $roleBadgeLabels = [
                                    0 => Locale::get('role_disabled'),
                                    1 => Locale::get('role_normal'),
                                    2 => Locale::get('role_admin')
                                ];
                                ?>
                                <span class="inline-block px-1 py-0.5 text-[9px] rounded-full <?= $roleBadgeClasses[$userRole] ?? 'bg-gray-100 text-gray-700' ?>">
                                    <?= $roleBadgeLabels[$userRole] ?? Locale::get('role_normal') ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-gray-400 italic"><?= Locale::get('system') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-400">
                        <?= htmlspecialchars($row['ip_address'] ?? '') ?>
                    </div>
                </div>
                <?php
                $hasDetails = $row['payload'] || $row['changes'] || 
                             ($row['record_owner_id'] && $row['record_owner_id'] != $row['user_id']);
                ?>
                <?php if ($hasDetails): ?>
                <div class="mt-2 pt-2 border-t border-gray-100 mt-auto">
                    <?php if ($row['record_owner_id'] && $row['record_owner_id'] != $row['user_id']): ?>
                        <div class="text-xs text-gray-500 mb-1">
                            <strong><?= Locale::get('record_owner') ?>:</strong>
                            <?= htmlspecialchars($row['record_owner_name'] ?? 'Unknown') ?>
                        </div>
                    <?php endif; ?>
                    <button onclick="toggleDetails('details-<?= $row['id'] ?>')" class="text-xs text-blue-500 hover:text-blue-700">
                        <?= Locale::get('view_details') ?> ▼
                    </button>
                    <div id="details-<?= $row['id'] ?>" class="hidden mt-2 p-2 bg-gray-50 rounded text-[10px]">
                        <?php if ($row['payload']): ?>
                            <div class="mb-1">
                                <strong><?= Locale::get('payload') ?>:</strong>
                                <pre class="whitespace-pre-wrap break-all text-gray-600"><?= htmlspecialchars($row['payload']) ?></pre>
                            </div>
                        <?php endif; ?>
                        <?php if ($row['changes']): ?>
                            <div>
                                <strong><?= Locale::get('changes') ?>:</strong>
                                <pre class="whitespace-pre-wrap break-all text-gray-600"><?= htmlspecialchars($row['changes']) ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDetails(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>

<?php
function getActionBadgeClass($action) {
    $action = strtolower($action);
    if (strpos($action, 'create') !== false || strpos($action, 'add') !== false) {
        return 'bg-green-100 text-green-700';
    } elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false || strpos($action, 'profile') !== false) {
        return 'bg-blue-100 text-blue-700';
    } elseif (strpos($action, 'delete') !== false || strpos($action, 'remove') !== false) {
        return 'bg-red-100 text-red-700';
    } elseif (strpos($action, 'login') !== false || strpos($action, 'logout') !== false) {
        return 'bg-purple-100 text-purple-700';
    } elseif (strpos($action, 'verify') !== false) {
        return 'bg-emerald-100 text-emerald-700';
    } else {
        return 'bg-gray-100 text-gray-700';
    }
}
?>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
