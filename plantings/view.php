<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';

$idValue = $_GET['id'] ?? '';
$plantingId = is_string($idValue) ? (int) $idValue : 0;
$planting = null;
$pageError = null;

if ($plantingId < 1) {
    http_response_code(404);
} else {
    try {
        $database = new Database();
        $plantingModel = new Planting($database->connect());
        $planting = $plantingModel->getById($plantingId);
        if (!$planting) {
            http_response_code(404);
        }
    } catch (Throwable) {
        http_response_code(500);
        $pageError = 'Planting details could not be loaded right now. Please try again later.';
    }
}

$pageTitle = 'Planting Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Planting Details</h1>
        <p class="mt-1 text-sm text-slate-600">View the related farm, farmer, crop, and planting information.</p>
    </div>
    <a href="/FMidterm_Project/plantings/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">Back to Plantings</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php elseif (!$planting): ?>
    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="status">Planting record not found.</div>
<?php else: ?>
    <section class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <dl class="divide-y divide-slate-100">
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Planting ID</dt>
                <dd class="sm:col-span-2"><?= (int) $planting['planting_id'] ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farmer</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farm</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Crop</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['crop_name'] . ' — ' . $planting['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Planting date</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Area planted</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars((string) $planting['area_planted'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> hectares</dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Seed quantity</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars((string) $planting['seed_quantity'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Expected harvest date</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['expected_harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Status</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($planting['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 px-5 py-4">
            <?php if (isAdmin()): ?>
                <a href="/FMidterm_Project/plantings/edit.php?id=<?= (int) $planting['planting_id'] ?>" class="rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">Edit Planting</a>
                <a href="/FMidterm_Project/plantings/delete.php?id=<?= (int) $planting['planting_id'] ?>" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Delete Planting</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
