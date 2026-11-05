<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Crop.php';

$idValue = $_GET['id'] ?? '';
$cropId = is_string($idValue) ? (int) $idValue : 0;
$crop = null;
$pageError = null;

if ($cropId < 1) {
    http_response_code(404);
} else {
    try {
        $database = new Database();
        $cropModel = new Crop($database->connect());
        $crop = $cropModel->getById($cropId);
        if (!$crop) {
            http_response_code(404);
        }
    } catch (Throwable) {
        http_response_code(500);
        $pageError = 'Crop details could not be loaded right now. Please try again later.';
    }
}

$pageTitle = 'Crop Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Crop Details</h1>
        <p class="mt-1 text-sm text-slate-600">View the crop and growing information.</p>
    </div>
    <a href="/FMidterm_Project/crops/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">Back to Crops</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php elseif (!$crop): ?>
    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="status">Crop not found.</div>
<?php else: ?>
    <section class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <dl class="divide-y divide-slate-100">
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Crop ID</dt>
                <dd class="sm:col-span-2"><?= (int) $crop['crop_id'] ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Crop name</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($crop['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Variety</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($crop['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Category</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($crop['crop_category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Growing period</dt>
                <dd class="sm:col-span-2"><?= (int) $crop['growing_period_days'] ?> days</dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Recommended season</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($crop['recommended_season_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 px-5 py-4">
            <?php if (isAdmin()): ?>
                <a href="/FMidterm_Project/crops/edit.php?id=<?= (int) $crop['crop_id'] ?>" class="rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">Edit Crop</a>
                <a href="/FMidterm_Project/crops/delete.php?id=<?= (int) $crop['crop_id'] ?>" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Delete Crop</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
