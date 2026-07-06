<?php
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../models/Bag.php';

$bagModel = new Bag();
$bags = $bagModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    body { overflow: hidden; }
    #mainContent > header { display: none; }
    #mainContent { display: flex; flex-direction: column; height: 100vh; height: 100dvh; overflow: hidden; }
</style>

<div class="flex-1 flex items-center justify-center px-4 bg-gradient-to-br from-blue-50 to-indigo-100 overflow-hidden">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                <?= Locale::get('login') ?>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                <?= Locale::get('login_required') ?>
            </p>
        </div>
        
        <form action="index.php?action=login" method="POST" class="mt-8 space-y-6" onsubmit="return validateLoginForm()">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::getCsrfToken()) ?>">
            <input type="hidden" id="bag_id" name="bag_id" value="">
            
            <!-- Group Selector -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3"><?= Locale::get('select_group') ?></label>
                
                <!-- Selected Group Display (click to open picker) -->
                <div id="selectedBagDisplay" onclick="openBagPicker()" class="cursor-pointer bg-white border-2 border-gray-200 hover:border-blue-400 rounded-xl p-4 transition-all duration-200 shadow-sm hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div id="selectedBagIcon" class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div id="selectedBagName" class="text-gray-400 font-medium"><?= Locale::get('select_group') ?>...</div>
                            <div id="selectedBagDesc" class="text-xs text-gray-400 mt-0.5"></div>
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Bag Picker Popup -->
                <div id="bagPickerPopup" class="hidden mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="p-3 border-b border-gray-100">
                        <input type="text" id="bagSearch" placeholder="<?= Locale::get('search') ?>..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="max-h-60 overflow-y-auto">
                        <?php foreach ($bags as $bag): ?>
                            <?php
                            $thumbUrl = Bag::getThumbnailUrl($bag['picture'] ?? '');
                            $displayName = $bag['long_name'] ?? $bag['name'];
                            ?>
                            <div class="bag-option flex items-center gap-3 px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-50 last:border-b-0" 
                                 data-bag-id="<?= $bag['id'] ?>"
                                 data-bag-name="<?= htmlspecialchars($displayName) ?>"
                                 data-bag-desc="<?= htmlspecialchars($bag['description'] ?? '') ?>"
                                 data-bag-icon="<?= $thumbUrl ? htmlspecialchars($thumbUrl) : '' ?>"
                                 data-bag-initial="<?= strtoupper(substr($bag['name'], 0, 1)) ?>"
                                 onclick="selectBag(this)">
                                <?php if ($thumbUrl): ?>
                                    <img src="<?= $thumbUrl ?>" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                                        <?= strtoupper(substr($bag['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-800 text-sm truncate"><?= htmlspecialchars($displayName) ?></div>
                                    <?php if (!empty($bag['description'])): ?>
                                        <div class="text-xs text-gray-500 truncate"><?= htmlspecialchars($bag['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="check-icon hidden flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                    <input type="text" id="username" name="username" required 
                           style="text-transform: lowercase"
                           oninput="this.value = this.value.toLowerCase()"
                           class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="<?= Locale::get('username_placeholder') ?>">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('password') ?></label>
                    <input type="password" id="password" name="password" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="<?= Locale::get('password_placeholder') ?>">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <?= Locale::get('login') ?>
                </button>
            </div>
        </form>
        
        <div class="text-center">
            <select id="langSwitch" onchange="switchLanguage(this.value)" class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-lg transition appearance-none pr-8 cursor-pointer text-sm font-medium">
                <?php foreach (Locale::getAvailableLanguages() as $code => $name): ?>
                    <option value="<?= $code ?>" <?= Locale::getCurrentLanguage() === $code ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<script>
function switchLanguage(lang) {
    window.location.href = 'index.php?lang=' + lang;
}

var isPickerOpen = false;

function openBagPicker() {
    var popup = document.getElementById('bagPickerPopup');
    if (isPickerOpen) {
        popup.classList.add('hidden');
        isPickerOpen = false;
    } else {
        popup.classList.remove('hidden');
        isPickerOpen = true;
        document.getElementById('bagSearch').focus();
        document.getElementById('bagSearch').value = '';
        filterBags('');
    }
}

function selectBag(element) {
    var bagId = element.getAttribute('data-bag-id');
    var bagName = element.getAttribute('data-bag-name');
    var bagDesc = element.getAttribute('data-bag-desc');
    var bagIcon = element.getAttribute('data-bag-icon');
    var bagInitial = element.getAttribute('data-bag-initial');
    
    // Update hidden input
    document.getElementById('bag_id').value = bagId;
    
    // Update display
    document.getElementById('selectedBagName').textContent = bagName;
    document.getElementById('selectedBagName').className = 'text-gray-800 font-semibold';
    document.getElementById('selectedBagDesc').textContent = bagDesc;
    
    var iconContainer = document.getElementById('selectedBagIcon');
    if (bagIcon) {
        iconContainer.innerHTML = '<img src="' + bagIcon + '" alt="" class="w-14 h-14 rounded-xl object-cover">';
        iconContainer.className = 'w-14 h-14 rounded-xl flex-shrink-0 overflow-hidden';
    } else {
        iconContainer.innerHTML = '<span class="text-white font-bold text-xl">' + bagInitial + '</span>';
        iconContainer.className = 'w-14 h-14 rounded-xl bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center flex-shrink-0';
    }
    
    // Update selected state
    document.querySelectorAll('.bag-option').forEach(function(opt) {
        opt.classList.remove('bg-blue-50');
        opt.querySelector('.check-icon').classList.add('hidden');
    });
    element.classList.add('bg-blue-50');
    element.querySelector('.check-icon').classList.remove('hidden');
    
    // Update border style
    document.getElementById('selectedBagDisplay').classList.remove('border-gray-200');
    document.getElementById('selectedBagDisplay').classList.add('border-blue-500');
    
    // Close picker
    document.getElementById('bagPickerPopup').classList.add('hidden');
    isPickerOpen = false;
}

function filterBags(query) {
    var options = document.querySelectorAll('.bag-option');
    var lowerQuery = query.toLowerCase();
    
    options.forEach(function(opt) {
        var name = opt.getAttribute('data-bag-name').toLowerCase();
        var desc = opt.getAttribute('data-bag-desc').toLowerCase();
        if (name.indexOf(lowerQuery) !== -1 || desc.indexOf(lowerQuery) !== -1) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
}

document.getElementById('bagSearch').addEventListener('input', function() {
    filterBags(this.value);
});

// Close picker when clicking outside
document.addEventListener('click', function(e) {
    var display = document.getElementById('selectedBagDisplay');
    var popup = document.getElementById('bagPickerPopup');
    
    if (isPickerOpen && !display.contains(e.target) && !popup.contains(e.target)) {
        popup.classList.add('hidden');
        isPickerOpen = false;
    }
});

function validateLoginForm() {
    var bagId = document.getElementById('bag_id').value;
    if (!bagId) {
        alert('<?= Locale::get('select_group') ?>');
        document.getElementById('selectedBagDisplay').classList.add('border-red-500');
        document.getElementById('selectedBagDisplay').classList.remove('border-gray-200');
        return false;
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
