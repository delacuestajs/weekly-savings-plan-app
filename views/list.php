<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('savings_payments') ?></h1>
    
    <div class="bg-green-500 text-white p-4 rounded-lg mb-4 md:mb-6">
        <h2 class="text-lg md:text-xl font-semibold"><?= Locale::get('total_savings') ?>: $<?= number_format($total, 2) ?></h2>
    </div>

    <div class="flex flex-wrap gap-3 mb-4 md:mb-6">
        <a href="index.php?action=create" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('add_new_saving') ?></a>
    </div>

    <form method="GET" action="index.php" class="mb-4 md:mb-6">
        <input type="hidden" name="action" value="payments">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="filter_user" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('user') ?></label>
                <?php if (Auth::isAdmin()): ?>
                <select id="filter_user" name="user_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value=""><?= Locale::get('all_users') ?></option>
                    <?php foreach ($usersList as $userRow): ?>
                        <option value="<?= $userRow['id'] ?>" <?= ($filters['user_id'] == $userRow['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="text" value="<?= htmlspecialchars(Auth::getUserName()) ?>" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                <?php endif; ?>
            </div>
            <div>
                <label for="filter_method" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_method') ?></label>
                <select id="filter_method" name="payment_method" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value=""><?= Locale::get('all_methods') ?></option>
                    <option value="cash" <?= ($filters['payment_method'] === 'cash') ? 'selected' : '' ?>><?= Locale::get('cash') ?></option>
                    <option value="bank_transfer" <?= ($filters['payment_method'] === 'bank_transfer') ? 'selected' : '' ?>><?= Locale::get('bank_transfer') ?></option>
                </select>
            </div>
            <div>
                <label for="filter_month" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('month') ?></label>
                <input type="month" id="filter_month" name="month" value="<?= htmlspecialchars($filters['month']) ?>" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <a href="index.php?action=payments" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200 text-center"><?= Locale::get('clear_filters') ?></a>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('id') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('user') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('name') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('amount') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden sm:table-cell"><?= Locale::get('payment_method') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('status') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('attachment') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('date') ?></th>
                    <?php if (Auth::isAdmin()): ?>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $savings->fetch(PDO::FETCH_ASSOC)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-sm md:text-base"><?= $row['id'] ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <?php if (!empty($row['firstname'])): ?>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($row['picture'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['picture']) ?>" alt="" class="w-8 h-8 rounded-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-xs" style="display:none;">
                                        <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-xs">
                                        <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-purple-600 font-medium"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></span>
                                    <?php if (!empty($row['username'])): ?>
                                        <br><span class="text-gray-500 text-xs">(<?= htmlspecialchars($row['username']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm md:text-base"><?= htmlspecialchars($row['description'] ?? '') ?></td>
                    <td class="p-3 text-sm md:text-base font-medium">$<?= number_format($row['amount'], 2) ?></td>
                    <td class="p-3 text-sm md:text-base hidden sm:table-cell"><?= Locale::get($row['payment_method']) ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <?php
                        $statusClasses = [
                            'unverified' => 'bg-orange-100 text-orange-800',
                            'verified' => 'bg-green-100 text-green-800'
                        ];
                        $statusClass = $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-block px-2 py-1 text-xs md:text-sm rounded-full <?= $statusClass ?>">
                            <?= Locale::get($row['status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-sm md:text-base">
                        <?php if (!empty($row['attachment'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isPdf = $ext === 'pdf';
                            $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'file');
                            $icon = $isImage ? '🖼️' : '📎';
                            ?>
                            <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($row['attachment'] ?? '') ?>', '<?= htmlspecialchars($row['description'] ?? '') ?> - <?= Locale::get('attachment') ?>', '<?= $type ?>')" class="text-blue-500 hover:underline cursor-pointer">
                                <?= $icon ?> <?= Locale::get('view') ?>
                            </button>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm md:text-base"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <?php if (Auth::isAdmin()): ?>
                    <td class="p-3 text-sm md:text-base">
                        <div class="flex flex-wrap gap-2">
                            <?php if ($row['status'] === 'unverified'): ?>
                                <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded transition duration-200 text-sm"><?= Locale::get('edit') ?></a>
                                <a href="index.php?action=verify&id=<?= $row['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white font-medium py-1 px-3 rounded transition duration-200 text-sm" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('verify') ?></a>
                                <a href="index.php?action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded transition duration-200 text-sm" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm"><?= Locale::get('verified') ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
