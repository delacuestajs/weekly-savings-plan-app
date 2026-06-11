<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('savings_payments') ?></h1>
    
    <div class="bg-green-500 text-white p-4 rounded-lg mb-4 md:mb-6">
        <h2 class="text-lg md:text-xl font-semibold"><?= Locale::get('total_savings') ?>: $<?= number_format($total, 2) ?></h2>
    </div>

    <div class="flex flex-wrap gap-3 mb-4 md:mb-6">
        <a href="index.php?action=create" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('add_new_saving') ?></a>
    </div>

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
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden lg:table-cell"><?= Locale::get('attachment') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden md:table-cell"><?= Locale::get('date') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('actions') ?></th>
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
                                    <?php if (!empty($row['nickname'])): ?>
                                        <br><span class="text-gray-500 text-xs">(<?= htmlspecialchars($row['nickname']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm md:text-base"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="p-3 text-sm md:text-base font-medium">$<?= number_format($row['amount'], 2) ?></td>
                    <td class="p-3 text-sm md:text-base hidden sm:table-cell"><?= Locale::get($row['payment_method']) ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <?php
                        $statusClasses = [
                            'pending' => 'bg-orange-100 text-orange-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-block px-2 py-1 text-xs md:text-sm rounded-full <?= $statusClass ?>">
                            <?= Locale::get($row['status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-sm md:text-base hidden lg:table-cell">
                        <?php if (!empty($row['attachment'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isPdf = $ext === 'pdf';
                            $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'file');
                            $icon = $isImage ? '🖼️' : '📎';
                            ?>
                            <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($row['attachment']) ?>', '<?= htmlspecialchars($row['name']) ?> - Attachment', '<?= $type ?>')" class="text-blue-500 hover:underline cursor-pointer">
                                <?= $icon ?> <?= Locale::get('view') ?>
                            </button>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm md:text-base hidden md:table-cell"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <div class="flex flex-wrap gap-2">
                            <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded transition duration-200 text-sm"><?= Locale::get('edit') ?></a>
                            <a href="index.php?action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded transition duration-200 text-sm" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
