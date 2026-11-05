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
        html.night-mode .bg-emerald-50 { background-color: #064e3b !important; }
        html.night-mode .bg-red-50 { background-color: #450a0a !important; }
        html.night-mode .text-slate-900,
        html.night-mode .text-slate-800,
        html.night-mode .text-slate-700 { color: #f1f5f9 !important; }
        html.night-mode .text-slate-600,
        html.night-mode .text-slate-500 { color: #cbd5e1 !important; }
        html.night-mode .text-emerald-800 { color: #6ee7b7 !important; }
        html.night-mode .text-blue-800 { color: #93c5fd !important; }
        html.night-mode .text-red-800 { color: #fca5a5 !important; }
        html.night-mode .border-slate-100,
        html.night-mode .border-slate-200,
        html.night-mode .border-slate-300 { border-color: #475569 !important; }
        html.night-mode .ring-slate-200 { --tw-ring-color: #475569 !important; }
        html.night-mode input,
        html.night-mode select,
        html.night-mode textarea { background-color: #0f172a !important; color: #f1f5f9 !important; border-color: #475569 !important; }
        html.night-mode input::placeholder,
        html.night-mode textarea::placeholder { color: #94a3b8 !important; }
        #night-mode-toggle[aria-checked="true"] { background-color: #10b981 !important; }
        #night-mode-toggle[aria-checked="true"] .night-mode-knob { transform: translateX(1.25rem) !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="bg-emerald-800 text-white shadow">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="<?= $appBaseUrl ?>/" class="text-lg font-bold tracking-wide">
                    Agriculture Information System
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex flex-col items-end gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/70 bg-white/10 px-3.5 py-2 text-sm text-emerald-50 shadow-sm"
                              aria-label="Current role: <?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Staff' ?>">
                            <svg class="h-4 w-4 text-emerald-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" />
                            </svg>
                            <span class="text-emerald-200">Role</span>
                            <span class="font-semibold"><?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Staff' ?></span>
                        </span>
                        <label class="inline-flex items-center gap-2 text-sm text-emerald-50" for="night-mode-toggle">
                            <span>Night mode</span>
                            <button id="night-mode-toggle" type="button" role="switch" aria-checked="false" aria-label="Toggle night mode"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-400 transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-800">
                                <span class="night-mode-knob inline-block h-4 w-4 translate-x-1 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </label>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="mt-4 flex flex-wrap gap-2" aria-label="Main navigation">
                    <?php foreach ($navigation as $item): ?>
                        <a href="<?= $appBaseUrl . $item['url'] ?>"
                           class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-emerald-50 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-white">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <?= $item['icon'] ?>
                            </svg>
                            <?= htmlspecialchars($item['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= $appBaseUrl ?>/auth/logout.php"
                       class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-emerald-50 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9m0 0 3-3m-3 3 3 3" />
                        </svg>
                        Log out
                    </a>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    <?php if (isset($_SESSION['user_id'])): ?>
        <script>
            const nightModeToggle = document.getElementById('night-mode-toggle');
            const setNightMode = (enabled) => {
                document.documentElement.classList.toggle('night-mode', enabled);
                nightModeToggle.setAttribute('aria-checked', String(enabled));
                try {
                    localStorage.setItem('ais-theme', enabled ? 'night' : 'light');
                } catch (error) {}
            };

            setNightMode(document.documentElement.classList.contains('night-mode'));
            nightModeToggle.addEventListener('click', () => {
                setNightMode(!document.documentElement.classList.contains('night-mode'));
            });
        </script>
    <?php endif; ?>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
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
