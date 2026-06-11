<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('add_new_saving') ?></h1>

    <form action="index.php?action=store" method="POST" enctype="multipart/form-data" class="max-w-lg">
        <div class="mb-4">
            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('user') ?></label>
            <div class="flex items-center gap-3">
                <select id="user_id" name="user_id" onchange="updateUserBadge(this)" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value=""><?= Locale::get('select_user_optional') ?></option>
                <?php foreach ($users as $userRow): ?>
                    <option value="<?= $userRow['id'] ?>"><?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?><?= !empty($userRow['nickname']) ? ' (' . htmlspecialchars($userRow['nickname']) . ')' : '' ?></option>
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
                img.src = 'uploads/' + user.picture;
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
            if (userSelect && userSelect.value) {
                updateUserBadge(userSelect);
            }
        });
        </script>

        <div class="mb-4">
            <label for="created_at" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date') ?></label>
            <input type="datetime-local" id="created_at" name="created_at" value="<?= date('Y-m-d\TH:i') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('name') ?></label>
            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?></label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_method') ?></label>
            <select id="payment_method" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value=""><?= Locale::get('select_method') ?></option>
                <option value="cash"><?= Locale::get('cash') ?></option>
                <option value="bank_transfer"><?= Locale::get('bank_transfer') ?></option>
                <option value="credit_card"><?= Locale::get('credit_card') ?></option>
                <option value="debit_card"><?= Locale::get('debit_card') ?></option>
                <option value="check"><?= Locale::get('check') ?></option>
            </select>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?></label>
            <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="pending"><?= Locale::get('pending') ?></option>
                <option value="completed"><?= Locale::get('completed') ?></option>
                <option value="failed"><?= Locale::get('failed') ?></option>
            </select>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?></label>
            <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="mb-4">
            <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('attachment_label') ?></label>
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
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
            <a href="index.php" class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
