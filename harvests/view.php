<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Harvest.php';

$idValue = $_GET['id'] ?? '';
$harvestId = is_string($idValue) ? (int) $idValue : 0;
$harvest = null;
$pageError = null;

if ($harvestId < 1) {
    http_response_code(404);
} else {
    try {
        $database = new Database();
        $harvestModel = new Harvest($database->connect());
        $harvest = $harvestModel->getById($harvestId);
        if (!$harvest) {
            http_response_code(404);
        }
    } catch (Throwable) {
        http_response_code(500);
        $pageError = 'Harvest details could not be loaded right now. Please try again later.';
    }
}

$pageTitle = 'Harvest Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Harvest Details</h1>
        <p class="mt-1 text-sm text-slate-600">View the planting, farm, farmer, and crop related to this harvest.</p>
    </div>
    <a href="/FMidterm_Project/harvests/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">Back to Harvests</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php elseif (!$harvest): ?>
    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="status">Harvest record not found.</div>
<?php else: ?>
    <section class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <dl class="divide-y divide-slate-100">
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Harvest ID</dt>
                <dd class="sm:col-span-2"><?= (int) $harvest['harvest_id'] ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Planting record</dt>
                <dd class="sm:col-span-2">#<?= (int) $harvest['planting_id'] ?> — <?= htmlspecialchars($harvest['crop_name'] . ' (' . $harvest['variety'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farmer</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farm</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Planting date</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Harvest date</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Quantity</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars((string) $harvest['quantity_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> kg</dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Selling price per kg</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars((string) $harvest['selling_price_per_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Quality grade</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Remark</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($harvest['remark_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 px-5 py-4">
            <?php if (isAdmin()): ?>
                <a href="/FMidterm_Project/harvests/edit.php?id=<?= (int) $harvest['harvest_id'] ?>" class="rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">Edit Harvest</a>
                <a href="/FMidterm_Project/harvests/delete.php?id=<?= (int) $harvest['harvest_id'] ?>" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Delete Harvest</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
