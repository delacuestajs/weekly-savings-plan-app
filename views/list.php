<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= Locale::get('savings_payments') ?></h1>
        <div class="flex items-center gap-2">
            <div class="bg-green-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium">
                <?= Locale::get('total_savings') ?>: $<?= number_format($total, 2) ?>
            </div>
            <button onclick="openCreatePaymentModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition text-sm font-medium">
                + <?= Locale::get('add_new_saving') ?>
            </button>
        </div>
    </div>

    <form method="GET" action="<?= $basePath ?>/" class="mb-4">
        <input type="hidden" name="action" value="payments">
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2">
            <div>
                <label for="filter_user" class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('user') ?></label>
                <?php if (Auth::isAdmin()): ?>
                <select id="filter_user" name="user_id" onchange="this.form.submit()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_users') ?></option>
                    <?php foreach ($usersList as $userRow): ?>
                        <option value="<?= $userRow['id'] ?>" <?= ($filters['user_id'] == $userRow['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="text" value="<?= htmlspecialchars(Auth::getUserName()) ?>" readonly class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                <?php endif; ?>
            </div>
            <div>
                <label for="filter_method" class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('payment_method') ?></label>
                <select id="filter_method" name="payment_method" onchange="this.form.submit()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value=""><?= Locale::get('all_methods') ?></option>
                    <option value="cash" <?= ($filters['payment_method'] === 'cash') ? 'selected' : '' ?>><?= Locale::get('cash') ?></option>
                    <option value="bank_transfer" <?= ($filters['payment_method'] === 'bank_transfer') ? 'selected' : '' ?>><?= Locale::get('bank_transfer') ?></option>
                </select>
            </div>
            <div>
                <label for="filter_month" class="block text-xs font-medium text-gray-500 mb-1"><?= Locale::get('month') ?></label>
                <input type="month" id="filter_month" name="month" value="<?= htmlspecialchars($filters['month']) ?>" onchange="this.form.submit()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <a href="<?= $basePath ?>/?action=payments" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition text-sm text-center"><?= Locale::get('clear_filters') ?></a>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php while ($row = $savings->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition flex flex-col">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-400">#<?= $row['id'] ?></span>
                        <?php if (!empty($row['firstname'])): ?>
                            <div class="flex items-center gap-1.5">
                                <?php if (!empty($row['picture'])): ?>
                                    <?php $thumbUrl = User::getThumbnailUrl($row['picture']); ?>
                                    <?php if ($thumbUrl): ?>
                                    <img src="<?= $thumbUrl ?>" alt="" class="w-5 h-5 rounded-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-[8px]" style="display:none;">
                                        <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-[8px]">
                                        <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <span class="text-xs font-medium text-purple-600"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['description'] ?? '') ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">$<?= number_format($row['amount'], 2) ?></p>
                    <?php
                    $statusClasses = [
                        'unverified' => 'bg-orange-100 text-orange-700',
                        'verified' => 'bg-green-100 text-green-700'
                    ];
                    $statusClass = $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <span class="inline-block px-1.5 py-0.5 text-[10px] rounded-full <?= $statusClass ?>">
                        <?= Locale::get($row['status']) ?>
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                <span><?= Locale::get($row['payment_method']) ?></span>
                <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                <?php if (!empty($row['attachment'])): ?>
                    <?php
                    $ext = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $isPdf = $ext === 'pdf';
                    $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'file');
                    $icon = $isImage ? '🖼️' : '📎';
                    ?>
                    <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($row['attachment'] ?? '') ?>', '<?= htmlspecialchars($row['description'] ?? '') ?> - <?= Locale::get('attachment') ?>', '<?= $type ?>')" class="text-blue-500 hover:underline cursor-pointer">
                        <?= $icon ?> <?= Locale::get('attachment') ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php if (Auth::isAdmin()): ?>
            <div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-100 mt-auto">
                <?php if ($row['status'] === 'unverified'): ?>
                    <button onclick="openEditPaymentModal(<?= $row['id'] ?>)" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded text-xs cursor-pointer"><?= Locale::get('edit') ?></button>
                    <a href="<?= $basePath ?>/?action=verify&id=<?= $row['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white font-medium py-1 px-3 rounded text-xs" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('verify') ?></a>
                    <a href="<?= $basePath ?>/?action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded text-xs" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                <?php else: ?>
                    <span class="text-gray-400 text-xs"><?= Locale::get('verified') ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
