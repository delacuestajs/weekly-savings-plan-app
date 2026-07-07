<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-3 md:p-4">
    <h1 class="text-xl md:text-2xl font-bold text-gray-800 mb-4"><?= Locale::get('edit_expense') ?></h1>

    <form action="<?= $basePath ?>/?module=expense&action=update&id=<?= $expense['id'] ?>" method="POST">
        <?= Auth::csrfField() ?>
        <div class="space-y-4">
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?> *</label>
                <input type="text" id="description" name="description" value="<?= htmlspecialchars($expense['description']) ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?> *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?= $expense['amount'] ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition"><?= Locale::get('save') ?></button>
            <a href="<?= $basePath ?>/?module=activity" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
