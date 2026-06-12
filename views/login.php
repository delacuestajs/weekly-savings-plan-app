<?php require_once __DIR__ . '/../views/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                <?= Locale::get('login') ?>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                <?= Locale::get('login_required') ?>
            </p>
        </div>
        
        <form action="index.php?action=login" method="POST" class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('username') ?></label>
                    <input type="text" id="username" name="username" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="<?= Locale::get('username_placeholder') ?>">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('password') ?></label>
                    <input type="password" id="password" name="password" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="<?= Locale::get('password_placeholder') ?>">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
</script>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
