<?php require_once __DIR__ . '/../header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('add_new_group') ?></h1>

    <form action="<?= $basePath ?>/?module=bag&action=store&return=<?= urlencode($_SERVER['HTTP_REFERER'] ?? $basePath . '/?module=bag') ?>" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <?= Auth::csrfField() ?>
        
        <!-- Picture Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2"><?= Locale::get('group_badge') ?></label>
            <div class="flex items-center gap-4">
                <div id="picturePreview" class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                    <span class="text-white text-2xl font-bold" id="pictureInitials">?</span>
                </div>
                <div>
                    <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 px-4 rounded-lg transition inline-block">
                        <?= Locale::get('upload_picture') ?>
                        <input type="file" id="pictureInput" name="picture" accept="image/*" class="hidden" onchange="previewPicture(this)">
                    </label>
                    <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_short_name') ?> *</label>
            <input type="text" id="name" name="name" required placeholder="<?= Locale::get('group_short_name_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_short_name_hint') ?></p>
        </div>

        <div class="mb-4">
            <label for="long_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_long_name') ?></label>
            <input type="text" id="long_name" name="long_name" placeholder="<?= Locale::get('group_long_name_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_long_name_hint') ?></p>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_description') ?></label>
            <textarea id="description" name="description" rows="3" placeholder="<?= Locale::get('group_description_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?></label>
            <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="1"><?= Locale::get('active') ?></option>
                <option value="0"><?= Locale::get('role_disabled') ?></option>
            </select>
        </div>

        <script>
        function previewPicture(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('picturePreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full h-full object-cover">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
            <a href="javascript:history.back()" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
