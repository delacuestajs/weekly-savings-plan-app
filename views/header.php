<?php
require_once __DIR__ . '/../controllers/Auth.php';
Auth::startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Payment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div id="mainContent">
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-800"><?= Locale::get('app_name') ?></h1>
            </div>
            <div class="flex items-center gap-2">
                <?php if (Auth::isLoggedIn()): ?>
                    <span class="text-sm text-gray-600"><?= Auth::getUserName() ?></span>
                    
                    <a href="index.php?action=weekly" class="bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('weekly_plan') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </a>
                    <a href="index.php?action=payments" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('payments') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </a>
                    <?php if (Auth::isAdmin()): ?>
                        <a href="index.php?action=create" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('add_saving') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </a>
                        <a href="index.php?module=user" class="bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('users') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </a>
                        <a href="index.php?module=activity" class="bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('activities') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="openPasswordModal(false)" class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('change_password') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </button>
                    
                    <a href="index.php?action=logout" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('logout') ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                <?php endif; ?>
                
                <div class="relative ml-1">
                    <select id="langSwitch" onchange="switchLanguage(this.value)" class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-lg transition appearance-none pr-8 cursor-pointer text-sm font-medium">
                        <?php foreach (Locale::getAvailableLanguages() as $code => $name): ?>
                            <option value="<?= $code ?>" <?= Locale::getCurrentLanguage() === $code ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </header>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('change_password') ?></h2>
                <button onclick="closePasswordModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="index.php?action=change_password" method="POST" onsubmit="return validatePasswordChange()">
                <input type="hidden" id="is_forced_change" name="is_forced_change" value="0">
                
                <div id="current_password_group" class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('current_password') ?></label>
                    <input type="password" id="current_password" name="current_password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('new_password') ?></label>
                    <input type="password" id="new_password" name="new_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="new_password_confirm" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('confirm_new_password') ?></label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                    <button type="button" onclick="closePasswordModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="p-4 md:p-6">

<script>
function switchLanguage(lang) {
    window.location.href = 'index.php?lang=' + lang;
}

function openPasswordModal(isForced) {
    document.getElementById('passwordModal').classList.remove('hidden');
    document.getElementById('current_password').value = '';
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirm').value = '';
    
    var currentPasswordGroup = document.getElementById('current_password_group');
    var isForcedInput = document.getElementById('is_forced_change');
    
    if (isForced) {
        currentPasswordGroup.style.display = 'none';
        document.getElementById('current_password').removeAttribute('required');
        isForcedInput.value = '1';
    } else {
        currentPasswordGroup.style.display = 'block';
        document.getElementById('current_password').setAttribute('required', '');
        isForcedInput.value = '0';
    }
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

function validatePasswordChange() {
    var isForced = document.getElementById('is_forced_change').value === '1';
    var currentPassword = document.getElementById('current_password').value;
    var newPassword = document.getElementById('new_password').value;
    var newPasswordConfirm = document.getElementById('new_password_confirm').value;
    
    if (!isForced && !currentPassword) {
        alert('<?= Locale::get('current_password_required') ?>');
        document.getElementById('current_password').focus();
        return false;
    }
    
    if (!newPassword) {
        alert('<?= Locale::get('new_password_required') ?>');
        document.getElementById('new_password').focus();
        return false;
    }
    
    if (!newPasswordConfirm) {
        alert('<?= Locale::get('confirm_password_required') ?>');
        document.getElementById('new_password_confirm').focus();
        return false;
    }
    
    if (newPassword !== newPasswordConfirm) {
        alert('<?= Locale::get('passwords_not_match') ?>');
        document.getElementById('new_password_confirm').focus();
        return false;
    }
    
    return true;
}

document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});

<?php if (isset($_SESSION['show_password_change']) && $_SESSION['show_password_change']): ?>
document.addEventListener('DOMContentLoaded', function() {
    alert('<?= Locale::get('default_password_warning') ?>');
    openPasswordModal(true);
    <?php unset($_SESSION['show_password_change']); ?>
});
<?php endif; ?>
</script>
