<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../models/Bag.php';

$bagModel = new Bag();
$bagsList = $bagModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('edit_user') ?></h1>

    <form action="index.php?module=user&action=update&id=<?= $user['id'] ?>&return=<?= urlencode($_SERVER['HTTP_REFERER'] ?? 'index.php?module=user') ?>" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <?= Auth::csrfField() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="mb-4">
            <label for="comments" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('comments') ?></label>
            <textarea id="comments" name="comments" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($user['comments'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label for="bag_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('bag') ?></label>
            <select id="bag_id" name="bag_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <?php foreach ($bagsList as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($user['bag_id'] ?? null) == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="multiplier" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('savings_multiplier') ?></label>
            <div class="flex items-center gap-3">
                <input type="number" id="multiplier" name="multiplier" value="<?= $user['multiplier'] ?? 1 ?>" min="1" max="100" required class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <span class="text-sm text-gray-500"><?= Locale::get('multiply_weekly_goal') ?></span>
            </div>
            <p class="mt-1 text-xs text-gray-400"><?= Locale::get('week_x_multiplier') ?> = $<span id="preview_multiplier"><?= number_format(1000 * ($user['multiplier'] ?? 1)) ?></span></p>
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('role') ?></label>
            <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="1" <?= ($user['role'] ?? 1) == 1 ? 'selected' : '' ?>><?= Locale::get('role_normal') ?></option>
                <option value="2" <?= ($user['role'] ?? 1) == 2 ? 'selected' : '' ?>><?= Locale::get('role_admin') ?></option>
                <?php if (Auth::isSuperAdmin() && ($user['role'] ?? 1) >= 2): ?>
                <option value="3" <?= ($user['role'] ?? 1) == 3 ? 'selected' : '' ?>><?= Locale::get('role_superadmin') ?></option>
                <?php endif; ?>
                <option value="0" <?= ($user['role'] ?? 1) == 0 ? 'selected' : '' ?>><?= Locale::get('role_disabled') ?></option>
            </select>
        </div>

        <div class="mb-4">
            <a href="index.php?module=user&action=reset_password&id=<?= $user['id'] ?>" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <?= Locale::get('reset_password') ?>
            </a>
            <p class="mt-1 text-xs text-gray-400"><?= Locale::get('password_hint') ?></p>
        </div>

        <script>
        document.getElementById('multiplier').addEventListener('input', function() {
            var multiplier = parseInt(this.value) || 1;
            document.getElementById('preview_multiplier').textContent = (1000 * multiplier).toLocaleString();
        });
        </script>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
            <a href="javascript:history.back()" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
