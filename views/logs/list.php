<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="max-w-6xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('activity_logs') ?></h1>
    
    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
        <input type="hidden" name="module" value="log">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date_from') ?></label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date_to') ?></label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('action_by') ?></label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_users') ?></option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($filters['user_id'] ?? '') == $user['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('action') ?></label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_actions') ?></option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= ($filters['action'] ?? '') == $act ? 'selected' : '' ?>>
                            <?= Locale::get('log_' . $act, $act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition">
                <?= Locale::get('filter') ?>
            </button>
            <a href="index.php?module=log" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                <?= Locale::get('clear_filters') ?>
            </a>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left text-sm font-semibold text-gray-700"><?= Locale::get('timestamp') ?></th>
                    <th class="p-3 text-left text-sm font-semibold text-gray-700"><?= Locale::get('action_by') ?></th>
                    <th class="p-3 text-left text-sm font-semibold text-gray-700"><?= Locale::get('action') ?></th>
                    <th class="p-3 text-left text-sm font-semibold text-gray-700 hidden md:table-cell"><?= Locale::get('details') ?></th>
                    <th class="p-3 text-left text-sm font-semibold text-gray-700 hidden lg:table-cell"><?= Locale::get('ip_address') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs->rowCount() === 0): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500"><?= Locale::get('no_logs_found') ?></td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $logs->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3 text-sm whitespace-nowrap">
                            <?= date('M d, Y H:i:s', strtotime($row['created_at'])) ?>
                        </td>
                        <td class="p-3 text-sm">
                            <?php if ($row['user_fullname']): ?>
                                <span class="font-medium"><?= htmlspecialchars($row['user_fullname']) ?></span>
                                <?php
                                $userRole = $row['user_role'] ?? 1;
                                $roleBadgeClasses = [
                                    0 => 'bg-red-100 text-red-800',
                                    1 => 'bg-green-100 text-green-800',
                                    2 => 'bg-purple-100 text-purple-800'
                                ];
                                $roleBadgeLabels = [
                                    0 => Locale::get('role_disabled'),
                                    1 => Locale::get('role_normal'),
                                    2 => Locale::get('role_admin')
                                ];
                                ?>
                                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs rounded-full <?= $roleBadgeClasses[$userRole] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= $roleBadgeLabels[$userRole] ?? Locale::get('role_normal') ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500"><?= htmlspecialchars($row['username'] ?? 'System') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-sm">
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full <?= getActionBadgeClass($row['action']) ?>">
                                <?= Locale::get('log_' . $row['action'], $row['action']) ?>
                            </span>
                        </td>
                        <td class="p-3 text-sm hidden md:table-cell">
                            <?php
                            $hasDetails = $row['payload'] || $row['changes'] || 
                                         ($row['record_owner_id'] && $row['record_owner_id'] != $row['user_id']);
                            ?>
                            <?php if ($hasDetails): ?>
                                <button onclick="toggleDetails('details-<?= $row['id'] ?>')" class="text-blue-500 hover:text-blue-700 text-sm">
                                    <?= Locale::get('view_details') ?>
                                </button>
                                <div id="details-<?= $row['id'] ?>" class="hidden mt-2 p-2 bg-gray-100 rounded text-xs">
                                    <?php if ($row['record_owner_id'] && $row['record_owner_id'] != $row['user_id']): ?>
                                        <div class="mb-2 pb-2 border-b border-gray-300">
                                            <strong><?= Locale::get('record_owner') ?>:</strong>
                                            <span class="font-medium"><?= htmlspecialchars($row['record_owner_name'] ?? 'Unknown') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($row['payload']): ?>
                                        <div class="mb-1">
                                            <strong><?= Locale::get('payload') ?>:</strong>
                                            <pre class="whitespace-pre-wrap break-all"><?= htmlspecialchars($row['payload']) ?></pre>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($row['changes']): ?>
                                        <div>
                                            <strong><?= Locale::get('changes') ?>:</strong>
                                            <pre class="whitespace-pre-wrap break-all"><?= htmlspecialchars($row['changes']) ?></pre>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-sm text-gray-600 hidden lg:table-cell">
                            <?= htmlspecialchars($row['ip_address'] ?? '-') ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
        return 'bg-green-100 text-green-800';
    } elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) {
        return 'bg-blue-100 text-blue-800';
    } elseif (strpos($action, 'delete') !== false || strpos($action, 'remove') !== false) {
        return 'bg-red-100 text-red-800';
    } elseif (strpos($action, 'login') !== false) {
        return 'bg-purple-100 text-purple-800';
    } else {
        return 'bg-gray-100 text-gray-800';
    }
}
?>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
