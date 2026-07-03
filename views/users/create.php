<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../models/Bag.php';

$bagModel = new Bag();
$bagsList = $bagModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('add_new_user') ?></h1>

    <form action="index.php?module=user&action=store&return=<?= urlencode($_SERVER['HTTP_REFERER'] ?? 'index.php?module=user') ?>" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <?= Auth::csrfField() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                <input type="text" id="firstname" name="firstname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                <input type="text" id="lastname" name="lastname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                <input type="text" id="username" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                <input type="tel" id="telephone" name="telephone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="mb-4">
            <label for="comments" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('comments') ?></label>
            <textarea id="comments" name="comments" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="mb-4">
            <label for="bag_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('bag') ?></label>
            <select id="bag_id" name="bag_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <?php foreach ($bagsList as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= (Auth::getBagId() == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="multiplier" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('savings_multiplier') ?></label>
            <div class="flex items-center gap-3">
                <input type="number" id="multiplier" name="multiplier" value="1" min="1" max="100" required class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <span class="text-sm text-gray-500"><?= Locale::get('multiply_hint') ?></span>
            </div>
            <p class="mt-1 text-xs text-gray-400"><?= Locale::get('week') ?> 1 × <?= Locale::get('multiplier') ?> = $<span id="preview_multiplier">1,000</span></p>
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('role') ?></label>
            <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="1"><?= Locale::get('role_normal') ?></option>
                <option value="2"><?= Locale::get('role_admin') ?></option>
                <?php if (Auth::isSuperAdmin()): ?>
                <option value="3"><?= Locale::get('role_superadmin') ?></option>
                <?php endif; ?>
                <option value="0"><?= Locale::get('role_disabled') ?></option>
            </select>
        </div>

        <script>
        document.getElementById('multiplier').addEventListener('input', function() {
            var multiplier = parseInt(this.value) || 1;
            document.getElementById('preview_multiplier').textContent = (1000 * multiplier).toLocaleString();
        });
        </script>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
            <a href="javascript:history.back()" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
