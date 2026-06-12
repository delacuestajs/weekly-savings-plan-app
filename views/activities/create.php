<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('add_new_activity') ?></h1>
    
    <form action="index.php?module=activity&action=store" method="POST" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('name') ?> *</label>
            <input type="text" id="name" name="name" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="<?= Locale::get('activity_name_placeholder') ?>">
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?></label>
            <textarea id="description" name="description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="<?= Locale::get('activity_description_placeholder') ?>"></textarea>
        </div>
        
        <div>
            <label for="value" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('value') ?> ($) *</label>
            <input type="number" id="value" name="value" step="0.01" min="0" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="0.00">
        </div>
        
        <div>
            <label for="activity_date" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('activity_date') ?> *</label>
            <input type="date" id="activity_date" name="activity_date" required 
                   value="<?= date('Y-m-d') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        
        <div class="flex flex-wrap gap-3 pt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
            <a href="javascript:history.back()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
