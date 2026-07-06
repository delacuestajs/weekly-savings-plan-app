<?php
require_once __DIR__ . '/../controllers/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Bag.php';
Auth::startSession();

$appConfig = require __DIR__ . '/../config/config.php';
$basePath = $appConfig['base_path'];

// Load current bag/group data
$currentBag = null;
$currentBagThumbUrl = null;
if (Auth::isLoggedIn() && Auth::getBagId()) {
    $bagModel = new Bag();
    $currentBag = $bagModel->getActiveById(Auth::getBagId());
    if ($currentBag) {
        $currentBagThumbUrl = Bag::getThumbnailUrl($currentBag['picture'] ?? '');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Savings Payment System</title>
    <meta name="description" content="Track weekly savings payments for individuals or groups">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Savings">
    <link rel="manifest" href="<?= $basePath ?>/manifest.json">
    <link rel="icon" type="image/svg+xml" href="<?= $basePath ?>/uploads/icon.svg">
    <link rel="apple-touch-icon" href="<?= $basePath ?>/uploads/icon-180.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div id="mainContent">
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Left: Group Badge & Name -->
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <?php if ($currentBag): ?>
                        <button onclick="openAboutModal()" class="flex-shrink-0 hover:opacity-80 transition">
                            <?php if ($currentBagThumbUrl): ?>
                                <img src="<?= $currentBagThumbUrl ?>" alt="" class="w-10 h-10 rounded-lg object-cover shadow-sm border border-gray-200">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold shadow-sm">
                                    <?= strtoupper(substr($currentBag['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </button>
                        <button onclick="openAboutModal()" class="min-w-0 text-left hover:opacity-80 transition">
                            <h1 class="text-sm sm:text-base md:text-lg font-bold text-gray-800 truncate leading-tight">
                                <?= htmlspecialchars($currentBag['long_name'] ?? $currentBag['name']) ?>
                            </h1>
                        </button>
                    <?php else: ?>
                        <h1 class="text-sm sm:text-base md:text-lg font-bold text-gray-800"><?= Locale::get('app_name') ?></h1>
                    <?php endif; ?>
                </div>
                
                <!-- Right: User Info -->
                <?php if (Auth::isLoggedIn()): ?>
                <?php
                // Initialize user data for profile picture display
                $currentUser = new User();
                $currentUserData = $currentUser->getById(Auth::getUserId());
                $currentUserThumbUrl = User::getThumbnailUrl($currentUserData['picture'] ?? '');
                ?>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Mobile -->
                    <div class="flex md:hidden items-center gap-2">
                        <div class="flex flex-col items-end">
                            <div class="flex items-center gap-1.5">
                                <?php if ($currentUserThumbUrl): ?>
                                    <img src="<?= $currentUserThumbUrl ?>" alt="" class="w-6 h-6 rounded-full object-cover">
                                <?php endif; ?>
                                <span class="text-xs text-gray-600 font-medium"><?= Auth::getUserName() ?></span>
                            </div>
                            <?php if (Auth::isSuperAdmin()): ?>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-pink-100 text-pink-800 font-semibold mt-0.5">Superadmin</span>
                            <?php elseif (Auth::isAdmin()): ?>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-semibold mt-0.5">Admin</span>
                            <?php endif; ?>
                        </div>
                        <select id="langSwitchMobile" onchange="switchLanguage(this.value)" class="bg-gray-600 hover:bg-gray-700 text-white p-1 rounded-lg transition appearance-none pr-7 cursor-pointer text-[10px] font-medium">
                            <?php foreach (Locale::getAvailableLanguages() as $code => $name): ?>
                                <option value="<?= $code ?>" <?= Locale::getCurrentLanguage() === $code ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    <div class="relative">
                        <button onclick="toggleMobileMenu()" class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div id="mobileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-50 py-2 border border-gray-100">
                            <!-- Navigation Section -->
                            <?php if (Auth::isAdmin()): ?>
                            <a href="index.php?module=dashboard" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-gray-700 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                                </span>
                                <?= Locale::get('dashboard') ?>
                            </a>
                            <?php endif; ?>
                            <a href="index.php?action=weekly" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-teal-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </span>
                                <?= Locale::get('weekly_plan') ?>
                            </a>
                            <a href="index.php?action=payments" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </span>
                                <?= Locale::get('payments') ?>
                            </a>
                            <?php if (Auth::isAdmin()): ?>
                            <a href="index.php?module=user" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-purple-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </span>
                                <?= Locale::get('users') ?>
                            </a>
                            <a href="index.php?module=activity" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                </span>
                                <?= Locale::get('activities') ?>
                            </a>
                            <a href="index.php?module=log" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-gray-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <?= Locale::get('activity_logs') ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (Auth::isSuperAdmin()): ?>
                            <!-- Superadmin Section -->
                            <div class="border-t border-gray-100 my-1"></div>
                            <div class="px-3 py-1 text-[10px] font-semibold text-pink-600 uppercase bg-pink-50"><?= Locale::get('role_superadmin') ?></div>
                            <a href="index.php?module=bag" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="w-7 h-7 rounded-lg bg-pink-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </span>
                                <?= Locale::get('groups') ?>
                            </a>
                            <?php endif; ?>
                            
                            <div class="border-t border-gray-100 my-1"></div>
                            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase"><?= Locale::get('actions') ?></div>
                            <button onclick="openCreatePaymentModal(); toggleMobileMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                <span class="w-7 h-7 rounded-lg bg-green-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </span>
                                <?= Locale::get('add_new_saving') ?>
                            </button>
                            <button onclick="openProfileModal(); toggleMobileMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                <span class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span>
                                <?= Locale::get('my_profile') ?>
                            </button>
                            <button onclick="openPasswordModal(false); toggleMobileMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                <span class="w-7 h-7 rounded-lg bg-gray-400 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </span>
                                <?= Locale::get('change_password') ?>
                            </button>
                            <a href="index.php?action=logout" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <span class="w-7 h-7 rounded-lg bg-red-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </span>
                                <?= Locale::get('logout') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Icons + Username + Language + Menu -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Navigation Icons -->
                    <div class="flex items-center gap-1">
                        <?php if (Auth::isAdmin()): ?>
                        <a href="index.php?module=dashboard" class="bg-gray-700 hover:bg-gray-800 text-white p-2 rounded-lg transition" title="<?= Locale::get('dashboard') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        </a>
                        <?php endif; ?>
                        <a href="index.php?action=weekly" class="bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('weekly_plan') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </a>
                        <a href="index.php?action=payments" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('payments') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </a>
                        <?php if (Auth::isAdmin()): ?>
                        <a href="index.php?module=user" class="bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('users') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </a>
                        <a href="index.php?module=activity" class="bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('activities') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </a>
                        <a href="index.php?module=log" class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('activity_logs') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (Auth::isSuperAdmin()): ?>
                    <!-- Superadmin Section -->
                    <div class="w-px h-6 bg-gray-200"></div>
                    <div class="flex items-center gap-1">
                        <a href="index.php?module=bag" class="bg-pink-500 hover:bg-pink-600 text-white p-2 rounded-lg transition" title="<?= Locale::get('groups') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="w-px h-6 bg-gray-200"></div>
                    
                    <!-- User Info + Actions Menu -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <?php if ($currentUserThumbUrl): ?>
                                <img src="<?= $currentUserThumbUrl ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                            <?php endif; ?>
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-700 font-medium leading-tight"><?= Auth::getUserName() ?></span>
                                <?php if (Auth::isSuperAdmin()): ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-pink-100 text-pink-800 font-semibold w-fit mt-0.5">Superadmin</span>
                                <?php elseif (Auth::isAdmin()): ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-semibold w-fit mt-0.5">Admin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="relative">
                            <select id="langSwitch" onchange="switchLanguage(this.value)" class="bg-gray-600 hover:bg-gray-700 text-white p-1 rounded-lg transition appearance-none pr-7 cursor-pointer text-xs font-medium">
                                <?php foreach (Locale::getAvailableLanguages() as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= Locale::getCurrentLanguage() === $code ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Desktop Menu Dropdown -->
                        <div class="relative">
                            <button onclick="toggleDesktopMenu()" class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <div id="desktopMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-50 py-2 border border-gray-100">
                                <!-- Actions Section -->
                                <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase"><?= Locale::get('actions') ?></div>
                                <button onclick="openCreatePaymentModal(); closeDesktopMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                    <span class="w-7 h-7 rounded-lg bg-green-500 flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </span>
                                    <?= Locale::get('add_new_saving') ?>
                                </button>
                                <button onclick="openProfileModal(); closeDesktopMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </span>
                                    <?= Locale::get('my_profile') ?>
                                </button>
                                <button onclick="openPasswordModal(false); closeDesktopMenu();" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                                    <span class="w-7 h-7 rounded-lg bg-gray-400 flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </span>
                                    <?= Locale::get('change_password') ?>
                                </button>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="index.php?action=logout" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                    <span class="w-7 h-7 rounded-lg bg-red-500 flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    </span>
                                    <?= Locale::get('logout') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- About Modal -->
    <div id="aboutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('about') ?></h2>
                <button onclick="closeAboutModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <?php if ($currentBag): ?>
            <!-- Group/Bag Info -->
            <div class="flex flex-col items-center mb-6 pb-6 border-b border-gray-200">
                <?php 
                $currentBagPictureUrl = Bag::getPictureUrl($currentBag['picture'] ?? '');
                if ($currentBagPictureUrl): ?>
                    <img src="<?= $currentBagPictureUrl ?>" alt="" class="w-72 h-72 rounded-2xl object-cover mb-4 shadow-lg">
                <?php elseif ($currentBagThumbUrl): ?>
                    <img src="<?= $currentBagThumbUrl ?>" alt="" class="w-72 h-72 rounded-2xl object-cover mb-4 shadow-lg">
                <?php else: ?>
                    <div class="w-72 h-72 rounded-2xl bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-7xl mb-4 shadow-lg">
                        <?= strtoupper(substr($currentBag['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h3 class="text-xl font-semibold text-gray-800 text-center"><?= htmlspecialchars($currentBag['long_name'] ?? $currentBag['name']) ?></h3>
                <?php if (!empty($currentBag['description'])): ?>
                    <p class="text-sm text-gray-600 text-center mt-2 px-6"><?= htmlspecialchars($currentBag['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- App Info -->
            <div class="text-center space-y-2">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-lg font-bold text-gray-800"><?= Locale::get('app_name') ?></span>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-medium"><?= Locale::get('version') ?>:</span> 
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($appConfig['app_version']) ?></span>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-medium"><?= Locale::get('build') ?>:</span> 
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($appConfig['app_build_date']) ?></span>
                </div>
            </div>
            
            <div class="flex justify-center mt-6">
                <button onclick="closeAboutModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('close') ?></button>
            </div>
        </div>
    </div>

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
                <?= Auth::csrfField() ?>
                
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

    <!-- Profile Modal -->
    <?php if (Auth::isLoggedIn()): ?>
    <?php
    // Reuse already loaded user data
    $profileData = $currentUserData;
    $profileThumbUrl = $currentUserThumbUrl;
    
    // Load users data for payment modals (exclude superadmin, filter by bag)
    $modalBagId = Auth::getBagId();
    $modalUsersStmt = $currentUser->getAll($modalBagId);
    $modalUsersArray = $modalUsersStmt->fetchAll(PDO::FETCH_ASSOC);
    $modalUsersData = [];
    foreach ($modalUsersArray as $row) {
        $modalUsersData[$row['id']] = [
            'id' => $row['id'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'picture' => $row['picture']
        ];
    }
    ?>
    <div id="profileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('my_profile') ?></h2>
                <button onclick="closeProfileModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="index.php?action=update_profile" method="POST" enctype="multipart/form-data">
                <?= Auth::csrfField() ?>
                
                <!-- Profile Picture -->
                <div class="flex flex-col items-center mb-4">
                    <div id="profilePicturePreview" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200 mb-3">
                        <?php if ($profileThumbUrl): ?>
                            <img src="<?= $profileThumbUrl ?>" alt="Profile" class="w-full h-full object-cover" id="profileThumbImg">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-2xl" id="profileInitials">
                                <?= strtoupper(substr($profileData['firstname'] ?? '', 0, 1) . substr($profileData['lastname'] ?? '', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2">
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-1.5 px-3 rounded-lg transition">
                            <?= Locale::get('upload_picture') ?>
                            <input type="file" id="profilePictureInput" name="picture" accept="image/*" class="hidden" onchange="previewProfilePicture(this)">
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1"><?= Locale::get('picture_hint') ?></p>
                </div>
                
                <div class="mb-4">
                    <label for="profile_firstname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('firstname') ?> *</label>
                    <input type="text" id="profile_firstname" name="firstname" required 
                           value="<?= htmlspecialchars($profileData['firstname'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="profile_lastname" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('lastname') ?> *</label>
                    <input type="text" id="profile_lastname" name="lastname" required 
                           value="<?= htmlspecialchars($profileData['lastname'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="profile_telephone" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('telephone') ?></label>
                    <input type="text" id="profile_telephone" name="telephone" 
                           value="<?= htmlspecialchars($profileData['telephone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="profile_email" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('email') ?> *</label>
                    <input type="email" id="profile_email" name="email" required
                           value="<?= htmlspecialchars($profileData['email'] ?? '') ?>"
                           placeholder="<?= Locale::get('email_placeholder') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                    <button type="button" onclick="closeProfileModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Payment Modal -->
    <div id="createPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('add_new_saving') ?></h2>
                <button onclick="closeCreatePaymentModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="createPaymentForm" action="index.php?action=store" method="POST" enctype="multipart/form-data" novalidate>
                <?= Auth::csrfField() ?>
                <div class="mb-4">
                    <label for="modal_user_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('user') ?> *</label>
                    <div class="flex items-center gap-3">
                        <select id="modal_user_id" name="user_id" onchange="updateModalUserBadge(this)" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= Auth::isAdmin() ? '' : 'bg-gray-100 text-gray-500 cursor-not-allowed opacity-70' ?>" <?= Auth::isAdmin() ? '' : 'disabled' ?>>
                            <option value=""><?= Locale::get('select_user') ?></option>
                        <?php foreach ($modalUsersArray as $userRow): ?>
                            <option value="<?= $userRow['id'] ?>" <?= (!Auth::isAdmin() && $userRow['id'] == Auth::getUserId()) ? 'selected' : '' ?>><?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?></option>
                        <?php endforeach; ?>
                        </select>
                        <?php if (!Auth::isAdmin()): ?>
                            <input type="hidden" name="user_id" value="<?= Auth::getUserId() ?>">
                        <?php endif; ?>
                        <div id="modalUserBadge" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gray-300"></div>
                    </div>
                    <input type="hidden" id="modalUsersData" value='<?= htmlspecialchars(json_encode($modalUsersData), ENT_QUOTES) ?>'>
                </div>

                <div class="mb-4">
                    <label for="modal_created_at" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date') ?> *</label>
                    <input type="date" id="modal_created_at" name="created_at" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="modal_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?> *</label>
                    <input type="text" id="modal_description" name="description" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="modal_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?> *</label>
                    <input type="number" id="modal_amount" name="amount" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="modal_payment_method" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_method') ?> *</label>
                    <select id="modal_payment_method" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value=""><?= Locale::get('select_method') ?></option>
                        <option value="cash"><?= Locale::get('cash') ?></option>
                        <option value="bank_transfer"><?= Locale::get('bank_transfer') ?></option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="modal_notes" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('notes') ?></label>
                    <textarea id="modal_notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <div class="mb-4">
                    <label for="modal_attachment" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('attachment_label') ?></label>
                    <input type="file" id="modal_attachment" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500"><?= Locale::get('allowed_files') ?></p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                    <button type="button" onclick="closeCreatePaymentModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div id="editPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= Locale::get('edit') ?> <?= Locale::get('payments') ?></h2>
                <button onclick="closeEditPaymentModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="editPaymentForm" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_saving_id" name="saving_id" value="">
                <?= Auth::csrfField() ?>
                
                <div class="mb-4">
                    <label for="edit_user_id" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('user') ?></label>
                    <div class="flex items-center gap-3">
                        <select id="edit_user_id" name="user_id" onchange="updateEditUserBadge(this)" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><?= Locale::get('select_user') ?></option>
                        <?php foreach ($modalUsersArray as $userRow): ?>
                            <option value="<?= $userRow['id'] ?>"><?= htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']) ?></option>
                        <?php endforeach; ?>
                        </select>
                        <div id="editUserBadge" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gray-300"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit_created_at" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('date') ?></label>
                    <input type="date" id="edit_created_at" name="created_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?> *</label>
                    <input type="text" id="edit_description" name="description" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="edit_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?> *</label>
                    <input type="number" id="edit_amount" name="amount" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="edit_payment_method" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('payment_method') ?> *</label>
                    <select id="edit_payment_method" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value=""><?= Locale::get('select_method') ?></option>
                        <option value="cash"><?= Locale::get('cash') ?></option>
                        <option value="bank_transfer"><?= Locale::get('bank_transfer') ?></option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('status') ?></label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="unverified"><?= Locale::get('unverified') ?></option>
                        <option value="verified"><?= Locale::get('verified') ?></option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="edit_notes" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('notes') ?></label>
                    <textarea id="edit_notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <div class="mb-4" id="edit_attachment_section">
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('attachment_label') ?></label>
                    <div id="edit_current_attachment" class="mb-2 p-3 bg-gray-50 rounded-lg hidden">
                        <p class="text-sm text-gray-600 mb-2"><?= Locale::get('current_attachment') ?></p>
                        <div id="edit_attachment_preview"></div>
                        <label class="flex items-center text-sm text-red-600 mt-2">
                            <input type="checkbox" name="remove_attachment" value="1" class="mr-2 rounded">
                            <?= Locale::get('remove_attachment') ?>
                        </label>
                    </div>
                    <div id="edit_no_attachment" class="mb-2 p-3 bg-gray-50 rounded-lg hidden">
                        <p class="text-sm text-gray-500 italic"><?= Locale::get('no_attachment') ?></p>
                    </div>
                    <input type="file" id="edit_attachment" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500"><?= Locale::get('allowed_files') ?></p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('update') ?></button>
                    <button type="button" onclick="closeEditPaymentModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Expense Modal (Create/Edit) -->
    <?php if (Auth::isAdmin()): ?>
    <div id="expenseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 id="expenseModalTitle" class="text-xl font-bold text-gray-800"><?= Locale::get('add_expense') ?></h2>
                <button onclick="closeExpenseModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="expenseForm" action="" method="POST">
                <?= Auth::csrfField() ?>
                <input type="hidden" id="expense_id" name="expense_id" value="">
                <input type="hidden" id="expense_activity_id" name="activity_id" value="">
                
                <div class="mb-4">
                    <label for="expense_description" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('description') ?> *</label>
                    <input type="text" id="expense_description" name="description" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="expense_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= Locale::get('amount') ?> *</label>
                    <input type="number" id="expense_amount" name="amount" step="0.01" min="0" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('save') ?></button>
                    <button type="button" onclick="closeExpenseModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('cancel') ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Credentials Modal (User Created / Password Reset) -->
    <?php if (isset($_SESSION['new_user_credentials'])): ?>
    <?php $newCreds = $_SESSION['new_user_credentials']; unset($_SESSION['new_user_credentials']); ?>
    <?php $isReset = $newCreds['reset'] ?? false; ?>
    <div id="credentialsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800"><?= $isReset ? Locale::get('password_reset') : Locale::get('user_created') ?></h2>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4"><?= $isReset ? Locale::get('password_reset_credentials_message') : Locale::get('credentials_message') ?></p>
                
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase"><?= Locale::get('name') ?></label>
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($newCreds['name']) ?></p>
                    </div>
                    <?php if (!empty($newCreds['group'])): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase"><?= Locale::get('group') ?></label>
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($newCreds['group']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase"><?= Locale::get('username') ?></label>
                        <p class="text-sm font-medium text-gray-800 font-mono"><?= htmlspecialchars($newCreds['username']) ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase"><?= Locale::get('password') ?></label>
                        <p class="text-sm font-medium text-gray-800 font-mono"><?= htmlspecialchars($newCreds['password']) ?></p>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-3"><?= Locale::get('credentials_note') ?></p>
            </div>
            
            <div class="flex justify-end">
                <button onclick="closeCredentialsModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200"><?= Locale::get('close') ?></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="flex-1 min-w-0 p-3 md:p-4 lg:p-6">

<script>
function switchLanguage(lang) {
    window.location.href = 'index.php?lang=' + lang;
}

function closeCredentialsModal() {
    var modal = document.getElementById('credentialsModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function toggleMobileMenu() {
    var menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

function toggleDesktopMenu() {
    var menu = document.getElementById('desktopMenu');
    menu.classList.toggle('hidden');
}

function closeDesktopMenu() {
    var menu = document.getElementById('desktopMenu');
    if (menu) {
        menu.classList.add('hidden');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    var menu = document.getElementById('mobileMenu');
    if (menu && !menu.classList.contains('hidden')) {
        var button = menu.previousElementSibling;
        if (!menu.contains(e.target) && !button.contains(e.target)) {
            menu.classList.add('hidden');
        }
    }
    
    var desktopMenu = document.getElementById('desktopMenu');
    if (desktopMenu && !desktopMenu.classList.contains('hidden')) {
        var desktopButton = desktopMenu.previousElementSibling;
        if (!desktopMenu.contains(e.target) && !desktopButton.contains(e.target)) {
            desktopMenu.classList.add('hidden');
        }
    }
});

function openAboutModal() {
    document.getElementById('aboutModal').classList.remove('hidden');
}

function closeAboutModal() {
    document.getElementById('aboutModal').classList.add('hidden');
}

var aboutModal = document.getElementById('aboutModal');
if (aboutModal) {
    aboutModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAboutModal();
        }
    });
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

var passwordModal = document.getElementById('passwordModal');
if (passwordModal) {
    passwordModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closePasswordModal();
        }
    });
}

function openProfileModal() {
    document.getElementById('profileModal').classList.remove('hidden');
}

function closeProfileModal() {
    document.getElementById('profileModal').classList.add('hidden');
}

var profileModal = document.getElementById('profileModal');
if (profileModal) {
    profileModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeProfileModal();
        }
    });
}

function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('profilePicturePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile" class="w-full h-full object-cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openExpenseModal(activityId, expenseId) {
    var modal = document.getElementById('expenseModal');
    var title = document.getElementById('expenseModalTitle');
    var form = document.getElementById('expenseForm');
    
    document.getElementById('expense_activity_id').value = activityId;
    document.getElementById('expense_id').value = expenseId || '';
    document.getElementById('expense_description').value = '';
    document.getElementById('expense_amount').value = '';
    
    if (expenseId) {
        title.textContent = '<?= Locale::get('edit_expense') ?>';
        form.action = 'index.php?module=expense&action=update&id=' + expenseId;
        // Fetch expense data via AJAX
        fetch('index.php?module=expense&action=get_json&id=' + expenseId)
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    document.getElementById('expense_description').value = data.description || '';
                    document.getElementById('expense_amount').value = data.amount || '';
                }
            });
    } else {
        title.textContent = '<?= Locale::get('add_expense') ?>';
        form.action = 'index.php?module=expense&action=store';
    }
    
    modal.classList.remove('hidden');
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
}

var expenseModal = document.getElementById('expenseModal');
if (expenseModal) {
    expenseModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeExpenseModal();
        }
    });
}

function openCreatePaymentModal() {
    document.getElementById('createPaymentModal').classList.remove('hidden');
    // Reset form
    document.getElementById('createPaymentForm').reset();
    document.getElementById('modal_created_at').value = '<?= date('Y-m-d') ?>';
    // Reset badge
    var badge = document.getElementById('modalUserBadge');
    badge.innerHTML = '';
    badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gray-300';
    // Trigger badge update if user is pre-selected
    var userSelect = document.getElementById('modal_user_id');
    if (userSelect && userSelect.value) {
        updateModalUserBadge(userSelect);
    }
}

function closeCreatePaymentModal() {
    document.getElementById('createPaymentModal').classList.add('hidden');
}

function updateModalUserBadge(select) {
    const badge = document.getElementById('modalUserBadge');
    const usersData = JSON.parse(document.getElementById('modalUsersData').value);
    const userId = select.value;
    
    if (!userId || !usersData[userId]) {
        badge.innerHTML = '';
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gray-300';
        return;
    }
    
    const user = usersData[userId];
    
    if (user.picture) {
        const img = document.createElement('img');
        const thumbPicture = user.picture.replace(/\.[^.]+$/, '_thumb.jpg');
        img.src = 'uploads/' + thumbPicture;
        img.alt = user.firstname + ' ' + user.lastname;
        img.className = 'w-10 h-10 rounded-full object-cover';
        img.onerror = function() {
            this.remove();
            badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
            badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
        };
        badge.innerHTML = '';
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0';
        badge.appendChild(img);
    } else {
        badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
    }
}

var createPaymentModal = document.getElementById('createPaymentModal');
if (createPaymentModal) {
    createPaymentModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreatePaymentModal();
        }
    });
}

var createPaymentForm = document.getElementById('createPaymentForm');
if (createPaymentForm) {
    createPaymentForm.addEventListener('submit', function(e) {
    var userSelect = document.getElementById('modal_user_id');
    var descriptionInput = document.getElementById('modal_description');
    var amountInput = document.getElementById('modal_amount');
    var methodSelect = document.getElementById('modal_payment_method');
    
    if (!userSelect.value) {
        e.preventDefault();
        alert('<?= Locale::get('user_required') ?>');
        userSelect.focus();
        return false;
    }
    
    if (!descriptionInput.value.trim()) {
        e.preventDefault();
        alert('<?= Locale::get('description_required') ?>');
        descriptionInput.focus();
        return false;
    }
    
    if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
        e.preventDefault();
        alert('<?= Locale::get('amount_required') ?>');
        amountInput.focus();
        return false;
    }
    
    if (!methodSelect.value) {
        e.preventDefault();
        alert('<?= Locale::get('method_required') ?>');
        methodSelect.focus();
        return false;
    }
});
}

function openEditPaymentModal(savingId) {
    // Fetch saving data via AJAX
    fetch('index.php?action=get_json&id=' + savingId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error loading payment data');
                return;
            }
            
            // Set form action
            document.getElementById('editPaymentForm').action = 'index.php?action=update&id=' + savingId;
            
            // Populate form fields
            document.getElementById('edit_user_id').value = data.user_id || '';
            document.getElementById('edit_created_at').value = data.created_at_formatted || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_amount').value = data.amount || '';
            document.getElementById('edit_payment_method').value = data.payment_method || '';
            document.getElementById('edit_status').value = data.status || 'unverified';
            document.getElementById('edit_notes').value = data.notes || '';
            
            // Update user badge
            updateEditUserBadge(document.getElementById('edit_user_id'));
            
            // Handle attachment
            var currentAttachment = document.getElementById('edit_current_attachment');
            var noAttachment = document.getElementById('edit_no_attachment');
            var attachmentPreview = document.getElementById('edit_attachment_preview');
            
            if (data.attachment) {
                currentAttachment.classList.remove('hidden');
                noAttachment.classList.add('hidden');
                
                var ext = data.attachment.split('.').pop().toLowerCase();
                var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
                
                if (isImage) {
                    attachmentPreview.innerHTML = '<img src="uploads/' + data.attachment + '" alt="Attachment" class="max-w-full h-auto max-h-32 rounded-lg">';
                } else {
                    attachmentPreview.innerHTML = '<a href="uploads/' + data.attachment + '" target="_blank" class="text-blue-500 hover:underline">📎 ' + data.attachment + '</a>';
                }
            } else {
                currentAttachment.classList.add('hidden');
                noAttachment.classList.remove('hidden');
            }
            
            // Show modal
            document.getElementById('editPaymentModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading payment data');
        });
}

function closeEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.add('hidden');
}

function updateEditUserBadge(select) {
    const badge = document.getElementById('editUserBadge');
    const usersData = JSON.parse(document.getElementById('modalUsersData').value);
    const userId = select.value;
    
    if (!userId || !usersData[userId]) {
        badge.innerHTML = '';
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gray-300';
        return;
    }
    
    const user = usersData[userId];
    
    if (user.picture) {
        const img = document.createElement('img');
        const thumbPicture = user.picture.replace(/\.[^.]+$/, '_thumb.jpg');
        img.src = 'uploads/' + thumbPicture;
        img.alt = user.firstname + ' ' + user.lastname;
        img.className = 'w-10 h-10 rounded-full object-cover';
        img.onerror = function() {
            this.remove();
            badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
            badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
        };
        badge.innerHTML = '';
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0';
        badge.appendChild(img);
    } else {
        badge.innerHTML = user.firstname.charAt(0).toUpperCase() + user.lastname.charAt(0).toUpperCase();
        badge.className = 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-gradient-to-br from-purple-400 to-blue-500';
    }
}

var editPaymentModal = document.getElementById('editPaymentModal');
if (editPaymentModal) {
    editPaymentModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditPaymentModal();
        }
    });
}

<?php if (isset($_SESSION['show_password_change']) && $_SESSION['show_password_change']): ?>
document.addEventListener('DOMContentLoaded', function() {
    alert('<?= Locale::get('default_password_warning') ?>');
    openPasswordModal(true);
});
<?php endif; ?>
</script>
<script>
// --- PWA: Service Worker ---
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= $basePath ?>/sw.js').catch(function() {});
}

// --- PWA: Back button logout confirmation ---
(function() {
    var isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (!isPWA) return;

    var isLoggedIn = <?= Auth::isLoggedIn() ? 'true' : 'false' ?>;
    if (!isLoggedIn) return;

    var pwaStrings = {
        title: <?= json_encode(Locale::get('pwa_logout_title')) ?>,
        confirm: <?= json_encode(Locale::get('pwa_logout_confirm')) ?>,
        yes: <?= json_encode(Locale::get('pwa_logout_yes')) ?>,
        cancel: <?= json_encode(Locale::get('pwa_logout_cancel')) ?>
    };

    history.pushState(null, '', location.href);

    var showingModal = false;

    window.addEventListener('popstate', function() {
        if (showingModal) return;
        if (!isLoggedIn) return;
        showingModal = true;

        var overlay = document.createElement('div');
        overlay.id = 'pwaLogoutModal';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center';
        overlay.innerHTML =
            '<div class="bg-white rounded-xl shadow-xl p-6 mx-4 max-w-sm w-full text-center">' +
            '<h3 class="text-lg font-semibold text-gray-800 mb-2">' + pwaStrings.title + '</h3>' +
            '<p class="text-gray-600 mb-6">' + pwaStrings.confirm + '</p>' +
            '<div class="flex gap-3">' +
            '<button id="pwaLogoutCancel" ' +
            'class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium">' +
            pwaStrings.cancel + '</button>' +
            '<button id="pwaLogoutConfirm" ' +
            'class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium">' +
            pwaStrings.yes + '</button>' +
            '</div></div>';
        document.body.appendChild(overlay);

        document.getElementById('pwaLogoutCancel').addEventListener('click', function() {
            overlay.remove();
            showingModal = false;
            history.pushState(null, '', location.href);
        });

        document.getElementById('pwaLogoutConfirm').addEventListener('click', function() {
            overlay.innerHTML = '<div class="bg-white rounded-xl shadow-xl p-8 mx-4 max-w-sm w-full text-center">' +
                '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div></div>';
            fetch('index.php?action=session_pwa_logout', { method: 'GET', keepalive: true })
                .then(function() { window.location.replace('index.php'); })
                .catch(function() { window.location.replace('index.php'); });
        });
    });
})();
</script>
