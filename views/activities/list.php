<?php require_once __DIR__ . '/../../views/header.php'; ?>

<div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= Locale::get('activities') ?></h1>
        <?php if (Auth::isAdmin()): ?>
        <button onclick="openCreateActivityModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition text-sm font-medium">
            + <?= Locale::get('add_new_activity') ?>
        </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php foreach ($expensesByActivity as $activityId => $data): ?>
    <?php $row = $data['activity']; ?>
    <?php $expenses = $data['expenses']; ?>
    <?php $totalExpenses = $data['total_expenses']; ?>
    
    <div class="border border-gray-200 rounded-lg overflow-hidden flex flex-col">
        <!-- Activity Header -->
        <div class="p-3 bg-gray-50">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">#<?= $row['id'] ?></span>
                        <span class="text-sm font-medium"><?= htmlspecialchars($row['name']) ?></span>
                    </div>
                    <?php if (!empty($row['description'])): ?>
                        <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($row['description']) ?></p>
                    <?php endif; ?>
                    <span class="text-xs text-gray-400"><?= date('M d, Y', strtotime($row['activity_date'])) ?></span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-orange-600">$<?= number_format($row['value'], 2) ?></div>
                    <?php if ($totalExpenses > 0): ?>
                        <div class="text-xs text-red-500"><?= Locale::get('expenses') ?>: $<?= number_format($totalExpenses, 2) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (Auth::isAdmin()): ?>
            <div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-200">
                <button onclick="openEditActivityModal(<?= $row['id'] ?>)" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-1 px-3 rounded text-xs cursor-pointer"><?= Locale::get('edit') ?></button>
                <a href="index.php?module=activity&action=delete&id=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded text-xs" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                <button onclick="openExpenseModal(<?= $row['id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1 px-3 rounded text-xs cursor-pointer"><?= Locale::get('add_expense') ?></button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Expenses List -->
        <?php if (!empty($expenses)): ?>
        <div class="border-t border-gray-200">
            <div class="px-3 py-2 bg-gray-100">
                <span class="text-xs font-semibold text-gray-500 uppercase"><?= Locale::get('expenses') ?></span>
            </div>
            <?php foreach ($expenses as $expense): ?>
            <div class="flex flex-wrap items-center justify-between px-3 py-2 border-b border-gray-100 last:border-b-0 <?= $expense['status'] === 'confirmed' ? 'bg-green-50' : '' ?>">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs text-gray-500">#<?= $expense['id'] ?></span>
                    <span class="text-xs truncate"><?= htmlspecialchars($expense['description']) ?></span>
                    <?php if ($expense['status'] === 'confirmed'): ?>
                        <span class="px-1.5 py-0.5 text-[9px] rounded-full bg-green-100 text-green-700"><?= Locale::get('confirmed') ?></span>
                    <?php else: ?>
                        <span class="px-1.5 py-0.5 text-[9px] rounded-full bg-yellow-100 text-yellow-700"><?= Locale::get('pending') ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-red-600">-$<?= number_format($expense['amount'], 2) ?></span>
                    <?php if (Auth::isAdmin() && $expense['status'] === 'pending'): ?>
                        <button onclick="openExpenseModal(<?= $row['id'] ?>, <?= $expense['id'] ?>)" class="bg-amber-400 hover:bg-amber-500 text-black font-medium py-0.5 px-1.5 rounded text-[9px] cursor-pointer"><?= Locale::get('edit') ?></button>
                        <a href="index.php?module=expense&action=confirm&id=<?= $expense['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white font-medium py-0.5 px-1.5 rounded text-[9px]"><?= Locale::get('confirm') ?></a>
                        <a href="index.php?module=expense&action=delete&id=<?= $expense['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white font-medium py-0.5 px-1.5 rounded text-[9px]" onclick="return confirm('<?= Locale::get('are_you_sure') ?>')"><?= Locale::get('delete') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
    
    <?php if (empty($expensesByActivity)): ?>
    <div class="text-center py-8 text-gray-500">
        <?= Locale::get('no_activities_found') ?>
    </div>
    <?php endif; ?>
</div>

<!-- Create Activity Modal -->
<div id="createActivityModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('add_new_activity') ?></h2>
            <button onclick="closeCreateActivityModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="createActivityForm" action="index.php?module=activity&action=store" method="POST">
            <?= Auth::csrfField() ?>
            
            <div class="mb-4">
                <label for="activity_create_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('name') ?> *</label>
                <input type="text" id="activity_create_name" name="name" required 
                       placeholder="<?= Locale::get('activity_name_placeholder') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="mb-4">
                <label for="activity_create_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?></label>
                <textarea id="activity_create_description" name="description" rows="3" 
                          placeholder="<?= Locale::get('activity_description_placeholder') ?>"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="activity_create_value" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('value') ?> ($) *</label>
                    <input type="number" id="activity_create_value" name="value" step="0.01" min="0" required 
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="activity_create_date" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('activity_date') ?> *</label>
                    <input type="date" id="activity_create_date" name="activity_date" required 
                           value="<?= date('Y-m-d') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                <button type="button" onclick="closeCreateActivityModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Activity Modal -->
<div id="editActivityModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('edit_activity') ?></h2>
            <button onclick="closeEditActivityModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="editActivityForm" action="" method="POST">
            <?= Auth::csrfField() ?>
            
            <div class="mb-4">
                <label for="activity_edit_name" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('name') ?> *</label>
                <input type="text" id="activity_edit_name" name="name" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="mb-4">
                <label for="activity_edit_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?></label>
                <textarea id="activity_edit_description" name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="activity_edit_value" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('value') ?> ($) *</label>
                    <input type="number" id="activity_edit_value" name="value" step="0.01" min="0" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="activity_edit_date" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('activity_date') ?> *</label>
                    <input type="date" id="activity_edit_date" name="activity_date" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
                <button type="button" onclick="closeEditActivityModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateActivityModal() {
    document.getElementById('createActivityModal').classList.remove('hidden');
    document.getElementById('createActivityForm').reset();
    document.getElementById('activity_create_date').value = '<?= date('Y-m-d') ?>';
}

function closeCreateActivityModal() {
    document.getElementById('createActivityModal').classList.add('hidden');
}

function openEditActivityModal(activityId) {
    document.getElementById('editActivityForm').action = 'index.php?module=activity&action=update&id=' + activityId;
    
    fetch('index.php?module=activity&action=get_json&id=' + activityId)
        .then(function(response) {
            var contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            window.location.href = 'index.php';
            throw new Error('Session expired');
        })
        .then(function(data) {
            if (data.error) {
                alert('<?= Locale::get('activity_not_found') ?>');
                return;
            }
            
            document.getElementById('activity_edit_name').value = data.name || '';
            document.getElementById('activity_edit_description').value = data.description || '';
            document.getElementById('activity_edit_value').value = data.value || '';
            document.getElementById('activity_edit_date').value = data.activity_date || '';
            
            document.getElementById('editActivityModal').classList.remove('hidden');
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('<?= Locale::get('error_loading_data') ?>');
        });
}

function closeEditActivityModal() {
    document.getElementById('editActivityModal').classList.add('hidden');
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    var createModal = document.getElementById('createActivityModal');
    var editModal = document.getElementById('editActivityModal');
    
    if (createModal) {
        createModal.addEventListener('click', function(e) {
            if (e.target === this) closeCreateActivityModal();
        });
    }
    
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeEditActivityModal();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>
