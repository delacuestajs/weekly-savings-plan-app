<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('activities') ?></h1>
    
    <div class="flex flex-wrap gap-3 mb-4 md:mb-6">
        <a href="index.php?module=activity&action=create" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('add_new_activity') ?></a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('id') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('name') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden sm:table-cell"><?= Locale::get('description') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('value') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('date') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $activities->fetch(PDO::FETCH_ASSOC)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-sm md:text-base"><?= $row['id'] ?></td>
                    <td class="p-3 text-sm md:text-base font-medium"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="p-3 text-sm md:text-base text-gray-600 hidden sm:table-cell">
                        <?php if (!empty($row['description'])): ?>
                            <?= htmlspecialchars(substr($row['description'], 0, 50)) ?><?= strlen($row['description']) > 50 ? '...' : '' ?>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm md:text-base font-medium text-orange-600">$<?= number_format($row['value'], 2) ?></td>
                    <td class="p-3 text-sm md:text-base"><?= date('M d, Y', strtotime($row['activity_date'])) ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <div class="flex flex-wrap gap-2">
                            <a href="index.php?module=activity&action=edit&id=<?= $row['id'] ?>" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded transition duration-200 text-sm"><?= Locale::get('edit') ?></a>
                            <a href="index.php?module=activity&action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded transition duration-200 text-sm" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
