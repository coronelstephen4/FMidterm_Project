<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';

$idValue = $_GET['id'] ?? '';
$farmId = is_string($idValue) ? (int) $idValue : 0;
$farm = null;
$pageError = null;

if ($farmId < 1) {
    http_response_code(404);
} else {
    try {
        $database = new Database();
        $farmModel = new Farm($database->connect());
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
        }
    } catch (Throwable) {
        http_response_code(500);
        $pageError = 'Farm details could not be loaded right now. Please try again later.';
    }
}

$pageTitle = 'Farm Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Farm Details</h1>
        <p class="mt-1 text-sm text-slate-600">View the farm and its associated farmer.</p>
    </div>
    <a href="/FMidterm_Project/farms/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">Back to Farms</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php elseif (!$farm): ?>
    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="status">Farm not found.</div>
<?php else: ?>
    <section class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <dl class="divide-y divide-slate-100">
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farm ID</dt>
                <dd class="sm:col-span-2"><?= (int) $farm['farm_id'] ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farm name</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farm['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farmer</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farm['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Location</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farm['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Area</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars((string) $farm['area_hectares'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> hectares</dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Soil type</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farm['soil_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Irrigation type</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farm['irrigation_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 px-5 py-4">
            <?php if (isAdmin()): ?>
                <a href="/FMidterm_Project/farms/edit.php?id=<?= (int) $farm['farm_id'] ?>" class="rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">Edit Farm</a>
                <a href="/FMidterm_Project/farms/delete.php?id=<?= (int) $farm['farm_id'] ?>" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Delete Farm</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
