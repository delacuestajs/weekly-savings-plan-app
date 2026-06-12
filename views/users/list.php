<?php require_once __DIR__ . '/../header.php'; ?>

<div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6"><?= Locale::get('users') ?></h1>

    <?php if (Auth::isAdmin()): ?>
    <div class="flex flex-wrap gap-3 mb-4 md:mb-6">
        <a href="index.php?module=user&action=create" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"><?= Locale::get('add_new_user') ?></a>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('id') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('picture') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('name') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden sm:table-cell"><?= Locale::get('username') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden md:table-cell"><?= Locale::get('telephone') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('role') ?></th>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700 hidden lg:table-cell"><?= Locale::get('comments') ?></th>
                    <?php if (Auth::isAdmin()): ?>
                    <th class="p-3 text-left text-sm md:text-base font-semibold text-gray-700"><?= Locale::get('actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-sm md:text-base"><?= $row['id'] ?></td>
                    <td class="p-3">
                        <div class="relative group">
                            <?php if (!empty($row['picture'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['picture']) ?>" 
                                     alt="Picture" 
                                     class="w-10 h-10 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-blue-500 transition"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm" style="display:none;">
                                    <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                </div>
                                <div class="absolute left-12 top-0 hidden group-hover:flex items-center gap-1 z-10 bg-white rounded-lg shadow-lg p-1">
                                    <button type="button" onclick="openModal('uploads/<?= htmlspecialchars($row['picture']) ?>', '<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>', 'image')" class="p-1 hover:bg-gray-100 rounded" title="<?= Locale::get('view') ?>">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button type="button" onclick="openZoomModal('uploads/<?= htmlspecialchars($row['picture']) ?>', '<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>')" class="p-1 hover:bg-gray-100 rounded" title="<?= Locale::get('zoom') ?>">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                    <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="p-3 text-sm md:text-base">
                        <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                    </td>
                    <td class="p-3 text-sm md:text-base hidden sm:table-cell"><?= htmlspecialchars($row['username'] ?? '-') ?></td>
                    <td class="p-3 text-sm md:text-base hidden md:table-cell"><?= htmlspecialchars($row['telephone'] ?? '-') ?></td>
                    <td class="p-3 text-sm md:text-base">
                        <?php
                        $role = $row['role'] ?? 1;
                        $roleClasses = [
                            0 => 'bg-red-100 text-red-800',
                            1 => 'bg-green-100 text-green-800',
                            2 => 'bg-purple-100 text-purple-800'
                        ];
                        $roleLabels = [
                            0 => Locale::get('role_disabled'),
                            1 => Locale::get('role_normal'),
                            2 => Locale::get('role_admin')
                        ];
                        $roleClass = $roleClasses[$role] ?? 'bg-gray-100 text-gray-800';
                        $roleLabel = $roleLabels[$role] ?? Locale::get('role_normal');
                        ?>
                        <span class="inline-block px-2 py-1 text-xs md:text-sm rounded-full <?= $roleClass ?>">
                            <?= $roleLabel ?>
                        </span>
                    </td>
                    <td class="p-3 text-sm md:text-base hidden lg:table-cell">
                        <?= htmlspecialchars(substr($row['comments'] ?? '-', 0, 30)) ?><?= strlen($row['comments'] ?? '') > 30 ? '...' : '' ?>
                    </td>
                    <?php if (Auth::isAdmin()): ?>
                    <td class="p-3 text-sm md:text-base">
                        <div class="flex flex-wrap gap-2">
                            <a href="index.php?module=user&action=edit&id=<?= $row['id'] ?>" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded transition duration-200 text-sm"><?= Locale::get('edit') ?></a>
                            <a href="index.php?module=user&action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded transition duration-200 text-sm" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
