<?php require_once __DIR__ . '/../header.php'; ?>
<?php require_once __DIR__ . '/../../models/Bag.php'; ?>
<?php
$bagModel = new Bag();
$allBags = $bagModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= Locale::get('users') ?></h1>
        <?php if (Auth::isAdmin()): ?>
        <button onclick="openUserCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition text-sm font-medium">
            + <?= Locale::get('add_new_user') ?>
        </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition flex flex-col">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0">
                        <?php if (!empty($row['picture'])): ?>
                            <?php $thumbUrl = User::getThumbnailUrl($row['picture']); ?>
                            <?php if ($thumbUrl): ?>
                            <img src="<?= $thumbUrl ?>" 
                                 alt="Picture" 
                                 class="w-10 h-10 rounded-full object-cover"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm" style="display:none;">
                                <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['lastname'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></p>
                        <?php if (!empty($row['username'])): ?>
                            <p class="text-xs text-gray-500">@<?= htmlspecialchars($row['username']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($row['telephone'])): ?>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($row['telephone']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php
                    $role = $row['role'] ?? 1;
                    $roleClasses = [
                        0 => 'bg-red-100 text-red-700',
                        1 => 'bg-green-100 text-green-700',
                        2 => 'bg-purple-100 text-purple-700',
                        3 => 'bg-pink-100 text-pink-700'
                    ];
                    $roleLabels = [
                        0 => Locale::get('role_disabled'),
                        1 => Locale::get('role_normal'),
                        2 => Locale::get('role_admin'),
                        3 => Locale::get('role_superadmin')
                    ];
                    $roleClass = $roleClasses[$role] ?? 'bg-gray-100 text-gray-700';
                    $roleLabel = $roleLabels[$role] ?? Locale::get('role_normal');
                    ?>
                    <span class="inline-block px-2 py-0.5 text-[10px] rounded-full <?= $roleClass ?>">
                        <?= $roleLabel ?>
                    </span>
                    <?php if (($row['multiplier'] ?? 1) > 1): ?>
                    <span class="inline-block px-2 py-0.5 text-[10px] rounded-full bg-blue-100 text-blue-700">
                        x<?= htmlspecialchars($row['multiplier']) ?>
                    </span>
                    <?php endif; ?>
                    <?php
                    $ps = $row['payment_system'] ?? 1;
                    $psClass = $ps == 2 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600';
                    $psLabel = $ps == 2 ? Locale::get('payment_system_fixed') : Locale::get('payment_system_week_number');
                    ?>
                    <span class="inline-block px-2 py-0.5 text-[10px] rounded-full <?= $psClass ?>">
                        <?= $psLabel ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($row['comments'])): ?>
                <p class="text-xs text-gray-400 mt-2"><?= htmlspecialchars($row['comments']) ?></p>
            <?php endif; ?>
            <?php if (Auth::isAdmin()): ?>
            <div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-100 mt-auto">
                <button onclick="openUserEditModal(<?= $row['id'] ?>)" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded text-xs"><?= Locale::get('edit') ?></button>
                <?php if (!in_array($row['id'], $usersWithVerifiedPayments)): ?>
                <a href="<?= $basePath ?>/?module=user&action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded text-xs" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Create User Modal -->
<div id="userCreateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('add_new_user') ?></h2>
            <button onclick="closeUserCreateModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="userCreateForm" action="<?= $basePath ?>/?module=user&action=store" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            
            <!-- Picture Upload -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= Locale::get('picture') ?></label>
                <div class="flex items-center gap-4">
                    <div id="userCreatePicturePreview" class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                        <span class="text-white text-xl font-bold" id="userCreatePictureInitial">?</span>
                    </div>
                    <div>
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 px-4 rounded-lg transition inline-block">
                            <?= Locale::get('upload_picture') ?>
                            <input type="file" id="userCreatePictureInput" name="picture" accept="image/*" class="hidden" onchange="previewUserCreatePicture(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_create_firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                    <input type="text" id="user_create_firstname" name="firstname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_create_lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                    <input type="text" id="user_create_lastname" name="lastname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_create_username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                    <input type="text" id="user_create_username" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_create_telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                    <input type="tel" id="user_create_telephone" name="telephone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="mb-4">
                <label for="user_create_email" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('email') ?> *</label>
                <input type="email" id="user_create_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= Locale::get('email_placeholder') ?>">
            </div>

            <div class="mb-4">
                <label for="user_create_comments" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('comments') ?></label>
                <textarea id="user_create_comments" name="comments" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <?php if (Auth::isSuperAdmin()): ?>
            <div class="mb-4">
                <label for="user_create_bag_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group') ?></label>
                <select id="user_create_bag_id" name="bag_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php foreach ($allBags as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= (Auth::getBagId() == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['long_name'] ?? $b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="bag_id" value="<?= Auth::getBagId() ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_create_multiplier" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('savings_multiplier') ?></label>
                    <input type="number" id="user_create_multiplier" name="multiplier" value="1" min="1" max="100" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_create_payment_system" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_system') ?></label>
                    <select id="user_create_payment_system" name="payment_system" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1"><?= Locale::get('payment_system_week_number') ?></option>
                        <option value="2"><?= Locale::get('payment_system_fixed') ?></option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_create_role" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('role') ?></label>
                    <select id="user_create_role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1"><?= Locale::get('role_normal') ?></option>
                        <option value="2"><?= Locale::get('role_admin') ?></option>
                        <?php if (Auth::isSuperAdmin()): ?>
                        <option value="3"><?= Locale::get('role_superadmin') ?></option>
                        <?php endif; ?>
                        <option value="0"><?= Locale::get('role_disabled') ?></option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                <button type="button" onclick="closeUserCreateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="userEditModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('edit_user') ?></h2>
            <button onclick="closeUserEditModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="userEditForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="user_edit_id" name="user_id" value="">
            <?= Auth::csrfField() ?>
            
            <!-- Picture Upload -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= Locale::get('picture') ?></label>
                <div class="flex items-center gap-4">
                    <div id="userEditPicturePreview" class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                        <span class="text-white text-xl font-bold" id="userEditPictureInitial">?</span>
                    </div>
                    <div>
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 px-4 rounded-lg transition inline-block">
                            <?= Locale::get('upload_picture') ?>
                            <input type="file" id="userEditPictureInput" name="picture" accept="image/*" class="hidden" onchange="previewUserEditPicture(this)">
                        </label>
                        <label id="userEditRemovePicture" class="hidden flex items-center text-sm text-red-600 mt-2 cursor-pointer">
                            <input type="checkbox" name="remove_picture" value="1" class="mr-2 rounded">
                            <?= Locale::get('remove') ?>
                        </label>
                        <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_edit_firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                    <input type="text" id="user_edit_firstname" name="firstname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_edit_lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                    <input type="text" id="user_edit_lastname" name="lastname" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_edit_username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                    <input type="text" id="user_edit_username" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_edit_telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                    <input type="tel" id="user_edit_telephone" name="telephone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="mb-4">
                <label for="user_edit_email" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('email') ?> *</label>
                <input type="email" id="user_edit_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= Locale::get('email_placeholder') ?>">
            </div>

            <div class="mb-4">
                <label for="user_edit_comments" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('comments') ?></label>
                <textarea id="user_edit_comments" name="comments" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <?php if (Auth::isSuperAdmin()): ?>
            <div class="mb-4">
                <label for="user_edit_bag_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group') ?></label>
                <select id="user_edit_bag_id" name="bag_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php foreach ($allBags as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['long_name'] ?? $b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p id="userEditBagWarning" class="hidden text-xs text-amber-600 mt-1"><?= Locale::get('cannot_move_user_verified_payments') ?></p>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_edit_multiplier" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('savings_multiplier') ?></label>
                    <input type="number" id="user_edit_multiplier" name="multiplier" min="1" max="100" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="user_edit_payment_system" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_system') ?></label>
                    <select id="user_edit_payment_system" name="payment_system" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1"><?= Locale::get('payment_system_week_number') ?></option>
                        <option value="2"><?= Locale::get('payment_system_fixed') ?></option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="user_edit_role" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('role') ?></label>
                    <select id="user_edit_role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1"><?= Locale::get('role_normal') ?></option>
                        <option value="2"><?= Locale::get('role_admin') ?></option>
                        <?php if (Auth::isSuperAdmin()): ?>
                        <option value="3"><?= Locale::get('role_superadmin') ?></option>
                        <?php endif; ?>
                        <option value="0"><?= Locale::get('role_disabled') ?></option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <button type="button" onclick="resetUserPassword()" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <?= Locale::get('reset_password') ?>
                </button>
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('password_hint') ?></p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
                <button type="button" onclick="closeUserEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
var usersWithVerifiedPayments = <?= json_encode($usersWithVerifiedPayments) ?>;

function openUserCreateModal() {
    document.getElementById('userCreateModal').classList.remove('hidden');
    document.getElementById('userCreateForm').reset();
    var preview = document.getElementById('userCreatePicturePreview');
    preview.innerHTML = '<span class="text-white text-xl font-bold">?</span>';
    preview.className = 'w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center';
}

function closeUserCreateModal() {
    document.getElementById('userCreateModal').classList.add('hidden');
}

function previewUserCreatePicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('userCreatePicturePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full h-full object-cover">';
            preview.className = 'w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openUserEditModal(userId) {
    document.getElementById('userEditForm').action = '<?= $basePath ?>/?module=user&action=update&id=' + userId;
    document.getElementById('user_edit_id').value = userId;
    
    fetch('<?= $basePath ?>/?module=user&action=get_json&id=' + userId)
        .then(function(response) {
            var contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            window.location.href = '<?= $basePath ?>/';
            throw new Error('Session expired');
        })
        .then(function(data) {
            if (data.error) {
                alert('Error loading user data');
                return;
            }
            
            document.getElementById('user_edit_firstname').value = data.firstname || '';
            document.getElementById('user_edit_lastname').value = data.lastname || '';
            document.getElementById('user_edit_username').value = data.username || '';
            document.getElementById('user_edit_telephone').value = data.telephone || '';
            document.getElementById('user_edit_email').value = data.email || '';
            document.getElementById('user_edit_comments').value = data.comments || '';
            document.getElementById('user_edit_multiplier').value = data.multiplier || '1';
            document.getElementById('user_edit_payment_system').value = data.payment_system || '1';
            document.getElementById('user_edit_role').value = data.role || '1';
            
            var bagSelect = document.getElementById('user_edit_bag_id');
            if (bagSelect) {
                bagSelect.value = data.bag_id || '';
                // Check if user has verified payments - disable bag select
                var hasVerifiedPayments = usersWithVerifiedPayments.indexOf(parseInt(userId, 10)) !== -1;
                var bagWarning = document.getElementById('userEditBagWarning');
                if (hasVerifiedPayments) {
                    bagSelect.setAttribute('disabled', '');
                    bagSelect.style.backgroundColor = '#f3f4f6';
                    bagSelect.style.color = '#6b7280';
                    bagSelect.style.cursor = 'not-allowed';
                    bagSelect.style.opacity = '0.7';
                    if (bagWarning) bagWarning.classList.remove('hidden');
                } else {
                    bagSelect.removeAttribute('disabled');
                    bagSelect.style.backgroundColor = '';
                    bagSelect.style.color = '';
                    bagSelect.style.cursor = '';
                    bagSelect.style.opacity = '';
                    if (bagWarning) bagWarning.classList.add('hidden');
                }
            }
            
            // Handle picture
            var preview = document.getElementById('userEditPicturePreview');
            var removeOption = document.getElementById('userEditRemovePicture');
            
            if (data.picture) {
                var thumbUrl = 'uploads/' + data.picture.replace(/\.[^.]+$/, '_thumb.jpg');
                preview.innerHTML = '<img src="' + thumbUrl + '" alt="" class="w-full h-full object-cover">';
                preview.className = 'w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200';
                removeOption.classList.remove('hidden');
            } else {
                var initials = (data.firstname ? data.firstname.charAt(0) : '') + (data.lastname ? data.lastname.charAt(0) : '');
                preview.innerHTML = '<span class="text-white text-xl font-bold">' + initials.toUpperCase() + '</span>';
                preview.className = 'w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center';
                removeOption.classList.add('hidden');
            }
            
            document.getElementById('userEditModal').classList.remove('hidden');
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function closeUserEditModal() {
    document.getElementById('userEditModal').classList.add('hidden');
}

function previewUserEditPicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('userEditPicturePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full h-full object-cover">';
            preview.className = 'w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200';
            document.getElementById('userEditRemovePicture').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function resetUserPassword() {
    var userId = document.getElementById('user_edit_id').value;
    if (confirm('<?= Locale::get('are_you_sure') ?>')) {
        window.location.href = '<?= $basePath ?>/?module=user&action=reset_password&id=' + userId;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var createModal = document.getElementById('userCreateModal');
    var editModal = document.getElementById('userEditModal');
    
    if (createModal) {
        createModal.addEventListener('click', function(e) {
            if (e.target === this) closeUserCreateModal();
        });
    }
    
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeUserEditModal();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
