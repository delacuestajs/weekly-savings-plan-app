<?php require_once __DIR__ . '/../header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6">Edit User</h1>

    <form action="index.php?module=user&action=update&id=<?= $user['id'] ?>" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Firstname *</label>
                <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Lastname *</label>
                <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="nickname" class="block text-sm font-medium text-gray-700 mb-1">Nickname</label>
                <input type="text" id="nickname" name="nickname" value="<?= htmlspecialchars($user['nickname'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="mb-4">
            <label for="picture" class="block text-sm font-medium text-gray-700 mb-1">Picture</label>
            <?php if (!empty($user['picture'])): ?>
                <div class="mb-2 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Current picture:</p>
                    <div class="flex items-center gap-4">
                        <img src="uploads/<?= htmlspecialchars($user['picture']) ?>" alt="Picture" class="w-20 h-20 rounded-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-xl" style="display:none;">
                            <?= strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)) ?>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button type="button" onclick="openZoomModal('uploads/<?= htmlspecialchars($user['picture']) ?>', 'Current Picture')" class="text-blue-500 hover:text-blue-700 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                Zoom
                            </button>
                            <label class="flex items-center text-sm text-red-600">
                                <input type="checkbox" name="remove_picture" value="1" class="mr-2 rounded">
                                Remove
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" id="picture" name="picture" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <div id="picture_progress" class="mt-2 hidden">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="progress-bar bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="progress-text text-xs text-gray-500 mt-1">0%</p>
            </div>
            <img id="picture_preview" src="" alt="Preview" class="mt-2 w-20 h-20 rounded-full object-cover hidden">
            <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF, WebP (max 5MB)</p>
        </div>

        <div class="mb-4">
            <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
            <textarea id="comments" name="comments" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($user['comments'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label for="multiplier" class="block text-sm font-medium text-gray-700 mb-1">Savings Multiplier</label>
            <div class="flex items-center gap-3">
                <input type="number" id="multiplier" name="multiplier" value="<?= $user['multiplier'] ?? 1 ?>" min="1" max="100" required class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <span class="text-sm text-gray-500">Multiply weekly savings goal</span>
            </div>
            <p class="mt-1 text-xs text-gray-400">Week 1 × multiplier = $<span id="preview_multiplier"><?= number_format(1000 * ($user['multiplier'] ?? 1)) ?></span></p>
        </div>

        <script>
        document.getElementById('multiplier').addEventListener('input', function() {
            var multiplier = parseInt(this.value) || 1;
            document.getElementById('preview_multiplier').textContent = (1000 * multiplier).toLocaleString();
        });
        </script>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Update</button>
            <a href="index.php?module=user" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
