<?php require_once __DIR__ . '/../header.php'; ?>

<?php
// Show download link if there's a pending dump file
if (isset($_SESSION['bag_truncate_download'])):
    $download = $_SESSION['bag_truncate_download'];
?>
<div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-sm text-green-800"><?= Locale::get('dump_created_successfully') ?>: <strong><?= htmlspecialchars($download['bag_name']) ?></strong></span>
    </div>
    <a href="index.php?module=bag&action=download_dump" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
        <?= Locale::get('download_dump') ?>
    </a>
</div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= Locale::get('groups') ?></h1>
        <button onclick="openCreateBagModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition text-sm font-medium">
            + <?= Locale::get('add_new_group') ?>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php while ($bag = $bags->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition flex flex-col">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0">
                        <?php if (!empty($bag['picture'])): ?>
                            <?php $thumbUrl = Bag::getThumbnailUrl($bag['picture']); ?>
                            <?php if ($thumbUrl): ?>
                            <img src="<?= $thumbUrl ?>" 
                                 alt="Badge" 
                                 class="w-10 h-10 rounded-lg object-cover"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm" style="display:none;">
                                <?= strtoupper(substr($bag['name'], 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                <?= strtoupper(substr($bag['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($bag['long_name'] ?? $bag['name']) ?></p>
                        <?php if (!empty($bag['long_name']) && $bag['long_name'] !== $bag['name']): ?>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($bag['name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php
                    $status = $bag['status'] ?? 1;
                    $isDeleted = !empty($bag['deleted_at']);
                    $statusClasses = [
                        0 => 'bg-red-100 text-red-700',
                        1 => 'bg-green-100 text-green-700'
                    ];
                    $statusLabels = [
                        0 => Locale::get('role_disabled'),
                        1 => Locale::get('active')
                    ];
                    if ($isDeleted) {
                        $statusClass = 'bg-gray-200 text-gray-600 line-through';
                        $statusLabel = Locale::get('disabled');
                    } else {
                        $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                        $statusLabel = $statusLabels[$status] ?? Locale::get('active');
                    }
                    ?>
                    <span class="inline-block px-2 py-0.5 text-[10px] rounded-full <?= $statusClass ?>">
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($bag['description'])): ?>
                <p class="text-xs text-gray-400 mt-2 line-clamp-2"><?= htmlspecialchars($bag['description']) ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-100 mt-auto">
                <button onclick="openEditBagModal(<?= $bag['id'] ?>)" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded text-xs"><?= Locale::get('edit') ?></button>
                <?php if ($bag['status'] == 1 && !in_array($bag['id'], $bagsWithVerifiedPayments)): ?>
                <a href="index.php?module=bag&action=disable&id=<?= $bag['id'] ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-1 px-3 rounded text-xs" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('disable') ?></a>
                <?php endif; ?>
                <button onclick="confirmTruncate(<?= $bag['id'] ?>, '<?= htmlspecialchars($bag['name'], ENT_QUOTES) ?>')" class="bg-red-600 hover:bg-red-700 text-white font-medium py-1 px-3 rounded text-xs"><?= Locale::get('truncate') ?></button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Create Bag Modal -->
<div id="createBagModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('add_new_group') ?></h2>
            <button onclick="closeCreateBagModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="createBagForm" action="index.php?module=bag&action=store" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            
            <!-- Picture Upload -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= Locale::get('group_badge') ?></label>
                <div class="flex items-center gap-4">
                    <div id="createPicturePreview" class="w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                        <span class="text-white text-xl font-bold" id="createPictureInitial">?</span>
                    </div>
                    <div>
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 px-4 rounded-lg transition inline-block">
                            <?= Locale::get('upload_picture') ?>
                            <input type="file" id="createPictureInput" name="picture" accept="image/*" class="hidden" onchange="previewCreatePicture(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="create_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_short_name') ?> *</label>
                <input type="text" id="create_name" name="name" required placeholder="<?= Locale::get('group_short_name_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_short_name_hint') ?></p>
            </div>

            <div class="mb-4">
                <label for="create_long_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_long_name') ?></label>
                <input type="text" id="create_long_name" name="long_name" placeholder="<?= Locale::get('group_long_name_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_long_name_hint') ?></p>
            </div>

            <div class="mb-4">
                <label for="create_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_description') ?></label>
                <textarea id="create_description" name="description" rows="3" placeholder="<?= Locale::get('group_description_placeholder') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="mb-4">
                <label for="create_status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?></label>
                <select id="create_status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1"><?= Locale::get('active') ?></option>
                    <option value="0"><?= Locale::get('role_disabled') ?></option>
                </select>
            </div>

            <div class="mb-4">
                <label for="bag_create_fixed_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('fixed_monthly_amount') ?></label>
                <input type="number" id="bag_create_fixed_amount" name="fixed_amount" value="<?= getenv('DEFAULT_FIXED_AMOUNT') ?: 50000 ?>" min="0" step="1000" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('fixed_monthly_amount_hint') ?></p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                <button type="button" onclick="closeCreateBagModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bag Modal -->
<div id="editBagModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('edit_group') ?></h2>
            <button onclick="closeEditBagModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="editBagForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit_bag_id" name="bag_id" value="">
            <?= Auth::csrfField() ?>
            
            <!-- Picture Upload -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= Locale::get('group_badge') ?></label>
                <div class="flex items-center gap-4">
                    <div id="editPicturePreview" class="w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                        <span class="text-white text-xl font-bold" id="editPictureInitial">?</span>
                    </div>
                    <div>
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 px-4 rounded-lg transition inline-block">
                            <?= Locale::get('upload_picture') ?>
                            <input type="file" id="editPictureInput" name="picture" accept="image/*" class="hidden" onchange="previewEditPicture(this)">
                        </label>
                        <label id="editRemovePicture" class="hidden flex items-center text-sm text-red-600 mt-2 cursor-pointer">
                            <input type="checkbox" name="remove_picture" value="1" class="mr-2 rounded">
                            <?= Locale::get('remove') ?>
                        </label>
                        <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="bag_edit_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_short_name') ?> *</label>
                <input type="text" id="bag_edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_short_name_hint') ?></p>
            </div>

            <div class="mb-4">
                <label for="bag_edit_long_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_long_name') ?></label>
                <input type="text" id="bag_edit_long_name" name="long_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('group_long_name_hint') ?></p>
            </div>

            <div class="mb-4">
                <label for="bag_edit_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('group_description') ?></label>
                <textarea id="bag_edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="mb-4">
                <label for="bag_edit_status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?></label>
                <input type="hidden" id="bag_edit_status_hidden" name="status" value="">
                <select id="bag_edit_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1"><?= Locale::get('active') ?></option>
                    <option value="0"><?= Locale::get('role_disabled') ?></option>
                </select>
                <p id="editStatusWarning" class="hidden text-xs text-amber-600 mt-1"><?= Locale::get('group_cannot_change_status') ?></p>
            </div>

            <div class="mb-4">
                <label for="bag_edit_fixed_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('fixed_monthly_amount') ?></label>
                <input type="number" id="bag_edit_fixed_amount" name="fixed_amount" min="0" step="1000" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1"><?= Locale::get('fixed_monthly_amount_hint') ?></p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
                <button type="button" onclick="closeEditBagModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
// Bags with verified payments (cannot change status)
var bagsWithVerifiedPayments = <?= json_encode(array_map('intval', $bagsWithVerifiedPayments)) ?>;

function openCreateBagModal() {
    document.getElementById('createBagModal').classList.remove('hidden');
    document.getElementById('createBagForm').reset();
    document.getElementById('createPicturePreview').innerHTML = '<span class="text-white text-xl font-bold">?</span>';
    document.getElementById('createPicturePreview').className = 'w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center';
}

function closeCreateBagModal() {
    document.getElementById('createBagModal').classList.add('hidden');
}

function previewCreatePicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('createPicturePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full h-full object-cover">';
            preview.className = 'w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openEditBagModal(bagId) {
    document.getElementById('editBagForm').action = 'index.php?module=bag&action=update&id=' + bagId;
    document.getElementById('edit_bag_id').value = bagId;
    
    // Fetch bag data
    fetch('index.php?module=bag&action=get_json&id=' + bagId)
        .then(function(response) {
            console.log('Response status:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            var contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            // If not JSON, session expired - redirect to login
            window.location.href = 'index.php';
            throw new Error('Session expired');
        })
        .then(function(data) {
            if (data.error) {
                alert('Error loading group data');
                return;
            }
            
            document.getElementById('bag_edit_name').value = data.name || '';
            document.getElementById('bag_edit_long_name').value = data.long_name || '';
            document.getElementById('bag_edit_description').value = data.description || '';
            document.getElementById('bag_edit_status').value = (data.status !== undefined && data.status !== null) ? String(data.status) : '1';
            document.getElementById('bag_edit_status_hidden').value = (data.status !== undefined && data.status !== null) ? String(data.status) : '1';
            document.getElementById('bag_edit_fixed_amount').value = data.fixed_amount || '50000';
            
            // Check if bag has verified payments - disable status if true
            var bagIdStr = String(bagId);
            var hasVerifiedPayments = bagsWithVerifiedPayments.some(function(id) { return String(id) === bagIdStr; });
            
            // Show modal first
            document.getElementById('editBagModal').classList.remove('hidden');
            
            // Apply disabled after delay
            var statusSelect = document.getElementById('bag_edit_status');
            var statusWarning = document.getElementById('editStatusWarning');
            
            if (hasVerifiedPayments) {
                setTimeout(function() {
                    statusSelect.setAttribute('disabled', '');
                    statusSelect.style.backgroundColor = '#f3f4f6';
                    statusSelect.style.color = '#6b7280';
                    statusSelect.style.cursor = 'not-allowed';
                    statusSelect.style.opacity = '0.7';
                    statusWarning.classList.remove('hidden');
                }, 100);
            } else {
                statusSelect.removeAttribute('disabled');
                statusSelect.style.backgroundColor = '';
                statusSelect.style.color = '';
                statusSelect.style.cursor = '';
                statusSelect.style.opacity = '';
                statusWarning.classList.add('hidden');
            }
            
            // Handle picture
            var preview = document.getElementById('editPicturePreview');
            var removeOption = document.getElementById('editRemovePicture');
            
            if (data.picture) {
                var thumbUrl = 'uploads/bags/' + data.picture.replace(/\.[^.]+$/, '_thumb.jpg');
                preview.innerHTML = '<img src="' + thumbUrl + '" alt="" class="w-full h-full object-cover">';
                preview.className = 'w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200';
                removeOption.classList.remove('hidden');
            } else {
                preview.innerHTML = '<span class="text-white text-xl font-bold">' + (data.name ? data.name.charAt(0).toUpperCase() : '?') + '</span>';
                preview.className = 'w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center';
                removeOption.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading group data');
        });
}

function closeEditBagModal() {
    document.getElementById('editBagModal').classList.add('hidden');
}

function previewEditPicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('editPicturePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full h-full object-cover">';
            preview.className = 'w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200';
            document.getElementById('editRemovePicture').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    var createModal = document.getElementById('createBagModal');
    var editModal = document.getElementById('editBagModal');
    var statusSelect = document.getElementById('bag_edit_status');
    
    if (createModal) {
        createModal.addEventListener('click', function(e) {
            if (e.target === this) closeCreateBagModal();
        });
    }
    
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeEditBagModal();
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            document.getElementById('bag_edit_status_hidden').value = this.value;
        });
    }
});

function confirmTruncate(bagId, bagName) {
    var message = '<?= Locale::get('truncate_confirm_message') ?>'.replace('{0}', bagName);
    if (confirm(message)) {
        var secondConfirm = '<?= Locale::get('truncate_second_confirm') ?>';
        if (confirm(secondConfirm)) {
            window.location.href = 'index.php?module=bag&action=truncate&id=' + bagId;
        }
    }
}
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
