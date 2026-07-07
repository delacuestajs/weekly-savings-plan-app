<?php require_once __DIR__ . '/header.php'; ?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('my_profile') ?></h1>

    <form action="<?= $basePath ?>/?action=update_profile" method="POST">
        <?= Auth::csrfField() ?>
        <div class="space-y-4">
            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                <input type="text" id="firstname" name="firstname" required 
                       value="<?= htmlspecialchars($user['firstname'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                <input type="text" id="lastname" name="lastname" required 
                       value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                <input type="text" id="telephone" name="telephone" 
                       value="<?= htmlspecialchars($user['telephone'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                <?= Locale::get('save') ?>
            </button>
            <a href="<?= $basePath ?>/" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200">
                <?= Locale::get('cancel') ?>
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
