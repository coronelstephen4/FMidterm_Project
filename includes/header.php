<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Agriculture Information System';
$appBaseUrl = '/FMidterm_Project';
$successMessage = $_SESSION['success'] ?? null;
$errorMessage = $_SESSION['error'] ?? null;

unset($_SESSION['success'], $_SESSION['error']);

$navigation = [
    ['label' => 'Dashboard', 'url' => '/', 'icon' => '<path d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v4h-6.5v-4Zm0 7.5h6.5v9h-6.5v-9Zm-10 3h6.5v6h-6.5v-6Z" />'],
    ['label' => 'Farmers', 'url' => '/farmers/', 'icon' => '<path d="M7.5 8.25a4.5 4.5 0 0 1 9 0m-12 0h15m-12.75 2.25h10.5M12 10.5a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-6.75 10.5a6.75 6.75 0 0 1 13.5 0" />'],
    ['label' => 'Farms', 'url' => '/farms/', 'icon' => '<path d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />'],
    ['label' => 'Crops', 'url' => '/crops/', 'icon' => '<path d="m4 7.5 16-3v13L4 20V7.5Zm2.5-.5v11.75m4.25-12.5v12m4.25-12.75v12m4.25-13v12" />'],
    ['label' => 'Plantings', 'url' => '/plantings/', 'icon' => '<path d="M12 21v-6m0 0c-2.75 0-4.5-1.75-4.5-4.5 2.75 0 4.5 1.75 4.5 4.5Zm0 0c2.75 0 4.5-1.75 4.5-4.5-2.75 0-4.5 1.75-4.5 4.5ZM4 21h16M6 18h12" />'],
    ['label' => 'Harvests', 'url' => '/harvests/', 'icon' => '<path d="M3 11h18l-1.5 9h-15L3 11Zm3 0 3-5m9 5-3-5m-3 5v9m-4-6v3m8-3v3" />'],
    ['label' => 'Reports', 'url' => '/reports/', 'icon' => '<path d="M3 3v18h18M7.5 14.25 11.25 10.5l3 3 5.25-6" />']
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        try {
            if (localStorage.getItem('ais-theme') === 'night') {
                document.documentElement.classList.add('night-mode');
            }
        } catch (error) {}
    </script>
    <style>
        html.night-mode body { background-color: #0f172a !important; color: #e2e8f0 !important; }
        html.night-mode .bg-white { background-color: #1e293b !important; }
        html.night-mode .bg-slate-50 { background-color: #0f172a !important; }
        html.night-mode .bg-slate-100 { background-color: #1e293b !important; }
        html.night-mode .bg-slate-200 { background-color: #334155 !important; }
        html.night-mode .bg-slate-800 { background-color: #047857 !important; }
        html.night-mode [class~="hover:bg-slate-900"]:hover { background-color: #065f46 !important; }
        html.night-mode [class~="hover:bg-slate-200"]:hover { background-color: #475569 !important; }
        html.night-mode [class~="hover:bg-slate-100"]:hover { background-color: #334155 !important; }
        html.night-mode [class~="hover:bg-slate-50"]:hover,
        html.night-mode [class~="hover:bg-white"]:hover { background-color: #334155 !important; color: #f1f5f9 !important; }
        html.night-mode .bg-emerald-50 { background-color: #064e3b !important; }
        html.night-mode [data-logo-badge] { background-color: #ecfdf5 !important; }
        html.night-mode .bg-emerald-100 { background-color: #065f46 !important; }
        html.night-mode .bg-red-50 { background-color: #450a0a !important; }
        html.night-mode .text-slate-900,
        html.night-mode .text-slate-800,
        html.night-mode .text-slate-700 { color: #f1f5f9 !important; }
        html.night-mode .text-slate-600,
        html.night-mode .text-slate-500 { color: #cbd5e1 !important; }
        html.night-mode .text-emerald-800,
        html.night-mode .text-emerald-900 { color: #ffffff !important; }
        html.night-mode .text-blue-800 { color: #93c5fd !important; }
        html.night-mode .text-red-800 { color: #fca5a5 !important; }
        html.night-mode .text-red-700 { color: #fca5a5 !important; }
        html.night-mode main table tbody tr:hover { background-color: #334155 !important; color: #f8fafc !important; }
        html.night-mode .border-slate-100,
        html.night-mode .border-slate-200,
        html.night-mode .border-slate-300 { border-color: #475569 !important; }
        html.night-mode .ring-slate-200 { --tw-ring-color: #475569 !important; }
        html.night-mode input,
        html.night-mode select,
        html.night-mode textarea { background-color: #0f172a !important; color: #f1f5f9 !important; border-color: #475569 !important; }
        html.night-mode input::placeholder,
        html.night-mode textarea::placeholder { color: #94a3b8 !important; }
        @media (max-width: 639px) {
            main .overflow-x-auto { -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
            main .overflow-x-auto table { min-width: max-content !important; }
            main .overflow-x-auto th,
            main .overflow-x-auto td { white-space: nowrap; }
        }
        @media print {
            @page { size: A4 portrait; margin: 0; }
            @page report-wide { size: A4 landscape; margin: 0; }
            html, body { background: #fff !important; color: #111827 !important; }
            body { min-height: auto !important; margin: 0 !important; padding: 10mm !important; }
            body[data-print-orientation="landscape"] main { page: report-wide; }
            body > header,
            body > footer,
            .no-print,
            main form { display: none !important; }
            main { max-width: none !important; margin: 0 !important; padding: 0 !important; }
            main, main * {
                color: #111827 !important;
                writing-mode: horizontal-tb !important;
                text-orientation: mixed !important;
            }
            html.night-mode main .text-slate-900,
            html.night-mode main .text-slate-800,
            html.night-mode main .text-slate-700,
            html.night-mode main .text-slate-600,
            html.night-mode main .text-slate-500,
            html.night-mode main .text-emerald-800,
            html.night-mode main .text-emerald-900,
            html.night-mode main .text-blue-800,
            html.night-mode main .text-red-800,
            html.night-mode main .text-red-700 { color: #111827 !important; }
            html.night-mode main table tbody tr:hover { color: #111827 !important; background: #fff !important; }
            main .overflow-x-auto {
                overflow: visible !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
            }
            main table {
                width: 100% !important;
                min-width: 0 !important;
                border-collapse: collapse !important;
                table-layout: auto !important;
                font-size: 8pt !important;
            }
            main thead { display: table-header-group; }
            main tr { break-inside: avoid; }
            main th, main td {
                border: 1px solid #cbd5e1 !important;
                padding: 5px !important;
                color: #111827 !important;
                background: #fff !important;
                white-space: normal !important;
                overflow-wrap: anywhere !important;
                text-align: left !important;
                vertical-align: middle !important;
            }
            main .bg-white { background: #fff !important; box-shadow: none !important; }
            main .bg-slate-50,
            main .bg-slate-100,
            main .bg-emerald-50,
            main .bg-emerald-100 { background: #fff !important; }
            main a { color: #111827 !important; text-decoration: none !important; }
        }
    </style>
    <?php if (!empty($reportPrint)): ?>
        <link rel="stylesheet" href="<?= $appBaseUrl ?>/assets/css/print.css">
        <?php
        $pageBoxTitle = json_encode((string) $pageTitle, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $pageBoxFooter = json_encode('Agriculture Information System | Generated: ' . ($reportPrintGenerated ?? date('Y-m-d H:i')), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        ?>
        <style>
            @media print {
                @page { @top-left { content: <?= $pageBoxTitle ?>; } @bottom-left { content: <?= $pageBoxFooter ?>; } }
                @page report-wide { @top-left { content: <?= $pageBoxTitle ?>; } @bottom-left { content: <?= $pageBoxFooter ?>; } }
            }
        </style>
    <?php endif; ?>
</head>
<body data-auto-print="<?= !empty($autoPrint) ? 'true' : 'false' ?>" data-print-orientation="<?= htmlspecialchars($printOrientation ?? 'portrait', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="min-h-screen bg-slate-50 text-slate-800">
    <header class="bg-emerald-800 text-white shadow">
        <div class="mx-auto max-w-[1680px] px-3 py-3 sm:px-6 sm:py-4 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">
                <a href="<?= $appBaseUrl ?>/" class="inline-flex min-w-0 items-center gap-2 rounded-md focus:outline-none focus:ring-2 focus:ring-white sm:gap-3">
                    <span data-logo-badge class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 shadow-sm ring-1 ring-emerald-200/70 sm:h-14 sm:w-14">
                        <svg class="h-8 w-8 sm:h-10 sm:w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 32 32" aria-hidden="true">
                            <path d="M16 4.5v8m0 0c-3.2 0-5.2-2-5.2-5.2 3.2 0 5.2 2 5.2 5.2Zm0 0c3.2 0 5.2-2 5.2-5.2-3.2 0-5.2 2-5.2 5.2Z" stroke="#166534" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                            <path d="M5 16.5h22M5.8 21c6.1-2.3 14.3-2.3 20.4 0M7.2 25.5c5.2-1.6 12.4-1.6 17.6 0" stroke="#65a30d" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-serif text-xl font-bold tracking-tight text-white sm:text-[1.75rem]">Agriculture</span>
                        <span class="mt-0.5 block text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-emerald-100 sm:mt-1 sm:text-sm sm:tracking-[0.16em]">Information System</span>
                    </span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex w-full items-center justify-start gap-2 sm:w-auto">
                        <span class="inline-flex h-9 items-center gap-1 rounded-full border border-emerald-500/70 bg-white/10 px-2 text-[0.65rem] text-emerald-50 shadow-sm sm:h-10 sm:gap-2 sm:px-3.5 sm:text-sm"
                              aria-label="Current role: <?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Staff' ?>">
                            <svg class="h-3.5 w-3.5 text-emerald-200 sm:h-4 sm:w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" />
                            </svg>
                            <span class="hidden text-emerald-200 sm:inline">Role</span>
                            <span class="font-semibold"><?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Staff' ?></span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="-ml-1.5 mt-3 grid grid-cols-3 justify-items-start gap-1.5 sm:-ml-3 sm:mt-4 sm:flex sm:flex-wrap sm:gap-2" aria-label="Main navigation">
                    <?php foreach ($navigation as $item): ?>
                        <a href="<?= $appBaseUrl . $item['url'] ?>"
                           class="inline-flex min-w-0 items-center justify-start gap-1 rounded-md px-1.5 py-2 text-xs font-medium text-emerald-50 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-white sm:gap-2 sm:px-3 sm:text-sm">
                            <svg class="h-4 w-4 shrink-0 sm:h-5 sm:w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <?= $item['icon'] ?>
                            </svg>
                            <?= htmlspecialchars($item['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                    <div id="settings-menu-container" class="relative col-span-3 justify-self-end sm:col-span-1 sm:ml-auto">
                        <button id="settings-menu-toggle" type="button" aria-label="Open settings menu" aria-expanded="false" aria-controls="settings-menu"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-emerald-50 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-white">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M3.75 12h16.5M3.75 18h16.5" />
                            </svg>
                        </button>
                        <div id="settings-menu" class="absolute right-0 top-full z-50 mt-2 hidden w-60 rounded-lg border border-slate-200 bg-white p-3 text-slate-800 shadow-xl" role="group" aria-label="Settings">
                            <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Settings</p>
                            <div class="flex items-center justify-between gap-3 rounded-md px-2 py-2">
                                <div>
                                    <p class="text-sm font-medium">Appearance</p>
                                    <p id="theme-mode-label" class="text-xs text-slate-500">Light mode</p>
                                </div>
                                <button id="night-mode-toggle" type="button" role="switch" aria-checked="false" aria-label="Switch to dark theme" title="Switch to dark theme"
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                    <svg data-theme-icon="sun" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-6.364-2.636 1.591-1.591M3 12h2.25m.386-6.364 1.591 1.591M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                    </svg>
                                    <svg data-theme-icon="moon" class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.267-2.598.752-3.752A9.753 9.753 0 0 0 2.25 11.25 9.75 9.75 0 0 0 12 21a9.753 9.753 0 0 0 9.752-5.998Z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="my-2 border-t border-slate-200"></div>
                            <a href="<?= $appBaseUrl ?>/auth/logout.php" class="flex items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-red-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9m0 0 3-3m-3 3 3 3" />
                                </svg>
                                Log out
                            </a>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    <?php if (isset($_SESSION['user_id'])): ?>
        <script>
            const nightModeToggle = document.getElementById('night-mode-toggle');
            const settingsMenuToggle = document.getElementById('settings-menu-toggle');
            const settingsMenu = document.getElementById('settings-menu');
            const settingsMenuContainer = document.getElementById('settings-menu-container');
            const closeSettingsMenu = () => {
                settingsMenu.classList.add('hidden');
                settingsMenuToggle.setAttribute('aria-expanded', 'false');
            };
            const setNightMode = (enabled) => {
                document.documentElement.classList.toggle('night-mode', enabled);
                nightModeToggle.setAttribute('aria-checked', String(enabled));
                document.getElementById('theme-mode-label').textContent = enabled ? 'Night mode' : 'Light mode';
                nightModeToggle.setAttribute('aria-label', enabled ? 'Switch to light theme' : 'Switch to dark theme');
                nightModeToggle.setAttribute('title', enabled ? 'Switch to light theme' : 'Switch to dark theme');
                nightModeToggle.querySelector('[data-theme-icon="sun"]').classList.toggle('hidden', enabled);
                nightModeToggle.querySelector('[data-theme-icon="moon"]').classList.toggle('hidden', !enabled);
                try {
                    localStorage.setItem('ais-theme', enabled ? 'night' : 'light');
                } catch (error) {}
            };

            setNightMode(document.documentElement.classList.contains('night-mode'));
            if (document.body.dataset.autoPrint === 'true') {
                window.addEventListener('load', () => {
                    const originalTitle = document.title;
                    document.title = '';
                    window.addEventListener('afterprint', () => {
                        document.title = originalTitle;
                    }, { once: true });
                    window.print();
                }, { once: true });
            }
            nightModeToggle.addEventListener('click', () => {
                setNightMode(!document.documentElement.classList.contains('night-mode'));
            });
            settingsMenuToggle.addEventListener('click', () => {
                const isOpen = !settingsMenu.classList.contains('hidden');
                settingsMenu.classList.toggle('hidden', isOpen);
                settingsMenuToggle.setAttribute('aria-expanded', String(!isOpen));
            });
            document.addEventListener('click', (event) => {
                if (!settingsMenuContainer.contains(event.target)) {
                    closeSettingsMenu();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !settingsMenu.classList.contains('hidden')) {
                    closeSettingsMenu();
                    settingsMenuToggle.focus();
                }
            });
        </script>
    <?php endif; ?>

    <main class="mx-auto max-w-[1680px] px-4 py-6 sm:px-6 lg:px-8">
        <?php if (is_string($successMessage) && $successMessage !== ''): ?>
            <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status" aria-live="polite">
                <?= htmlspecialchars($successMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>
