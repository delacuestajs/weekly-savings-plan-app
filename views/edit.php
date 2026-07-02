<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('edit') ?> <?= Locale::get('payments') ?></h1>

    <form action="index.php?action=update&id=<?= $saving['id'] ?>&return=<?= urlencode($_SERVER['HTTP_REFERER'] ?? 'index.php?action=payments') ?>" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <?= Auth::csrfField() ?>
        <div class="mb-4">
            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('user') ?></label>
            <div class="flex items-center gap-3">
                <select id="user_id" name="user_id" onchange="updateUserBadge(this)" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value=""><?= Locale::get('select_user') ?></option>
                <?php foreach ($users as $userRow): ?>
                    <option value="<?= $userRow['id'] ?>" <?= ($saving['user_id'] == $userRow['id']) ? 'selected' : '' ?>><?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?><?= !empty($userRow['username']) ? ' (' . htmlspecialchars($userRow['username']) . ')' : '' ?></option>
                <?php endforeach; ?>
                </select>
                <div id="userBadge" class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg flex-shrink-0 bg-gray-300"></div>
            </div>
            <input type="hidden" id="usersData" value='<?= htmlspecialchars(json_encode($usersData), ENT_QUOTES) ?>'>
        </div>

        <script>
        function updateUserBadge(select) {
            const badge = document.getElementById('userBadge');
            const usersData = JSON.parse(document.getElementById('usersData').value);
            const userId = select.value;
            
            if (!userId || !usersData[userId]) {
                badge.innerHTML = '';
                badge.className = 'w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg flex-shrink-0 bg-gray-300';
                return;
            }
            
            const user = usersData[userId];
            
            if (user.picture) {
                const img = document.createElement('img');
                // Use thumbnail if available
                const thumbPicture = user.picture.replace(/\.[^.]+$/, '_thumb.jpg');
                img.src = 'uploads/' + thumbPicture;
                img.alt = user.firstname + ' ' + user.lastname;
                img.className = 'w-12 h-12 rounded-full object-cover';
                img.onerror = function() {
                    this.remove();
                    badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
                    badge.className = 'w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
                };
                badge.innerHTML = '';
                badge.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0';
                badge.appendChild(img);
            } else {
                badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
                badge.className = 'w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            var userSelect = document.getElementById('user_id');
            if (userSelect.value) {
                updateUserBadge(userSelect);
            }
        });
        </script>

        <div class="mb-4">
            <label for="created_at" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date') ?></label>
            <?php
            $currentDate = '';
            if (!empty($saving['created_at'])) {
                $dateObj = new DateTime($saving['created_at']);
                $currentDate = $dateObj->format('Y-m-d\TH:i');
            } else {
                $currentDate = date('Y-m-d\TH:i');
            }
            ?>
            <input type="datetime-local" id="created_at" name="created_at" value="<?= $currentDate ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?> *</label>
            <input type="text" id="description" name="description" value="<?= htmlspecialchars($saving['description']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?> *</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" 
                   value="<?= $saving['amount'] ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_method') ?> *</label>
            <select id="payment_method" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value=""><?= Locale::get('select_method') ?></option>
                <option value="cash" <?= $saving['payment_method'] === 'cash' ? 'selected' : '' ?>><?= Locale::get('cash') ?></option>
                <option value="bank_transfer" <?= $saving['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>><?= Locale::get('bank_transfer') ?></option>
            </select>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?> *</label>
            <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="unverified" <?= $saving['status'] === 'unverified' ? 'selected' : '' ?>><?= Locale::get('unverified') ?></option>
                <option value="verified" <?= $saving['status'] === 'verified' ? 'selected' : '' ?>><?= Locale::get('verified') ?></option>
            </select>
        </div>

        <div class="mb-4">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('notes') ?></label>
            <textarea id="notes" name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($saving['notes'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('attachment_label') ?></label>
            <?php if (!empty($saving['attachment'])): ?>
                <div class="mb-2 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2"><?= Locale::get('current_attachment') ?></p>
                    <?php
                    $ext = strtolower(pathinfo($saving['attachment'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $isPdf = $ext === 'pdf';
                    $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'file');
                    ?>
                    <?php if ($isImage): ?>
                        <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($saving['attachment']) ?>', '<?= Locale::get('current_attachment') ?>', '<?= $type ?>')" class="cursor-pointer">
                            <img src="uploads/<?= htmlspecialchars($saving['attachment']) ?>" alt="Attachment" class="max-w-full h-auto max-h-48 rounded-lg mb-2 hover:opacity-80 transition">
                        </button>
                    <?php else: ?>
                        <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($saving['attachment']) ?>', '<?= Locale::get('current_attachment') ?>', '<?= $type ?>')" class="text-blue-500 hover:underline mb-2 inline-block cursor-pointer">
                            📎 <?= htmlspecialchars($saving['attachment']) ?>
                        </button>
                    <?php endif; ?>
                    <label class="flex items-center text-sm text-red-600">
                        <input type="checkbox" name="remove_attachment" value="1" class="mr-2 rounded">
                        <?= Locale::get('remove_attachment') ?>
                    </label>
                </div>
            <?php else: ?>
                <div class="mb-2 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 italic"><?= Locale::get('no_attachment') ?></p>
                </div>
            <?php endif; ?>
            <input type="file" id="attachment" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <div id="attachment_progress" class="mt-2 hidden">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="progress-bar bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="progress-text text-xs text-gray-500 mt-1">0%</p>
            </div>
            <p class="mt-1 text-sm text-gray-500"><?= Locale::get('allowed_files') ?></p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
            <a href="javascript:history.back()" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
