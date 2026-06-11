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
                <a href="index.php?action=weekly" class="bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('weekly_plan') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </a>
                <a href="index.php?action=payments" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('payments') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </a>
                <a href="index.php?action=create" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('add_saving') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </a>
                <a href="index.php?module=user" class="bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('users') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </a>
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

    <div class="p-4 md:p-6">
<script>
function switchLanguage(lang) {
    window.location.href = 'index.php?lang=' + lang;
}
</script>
