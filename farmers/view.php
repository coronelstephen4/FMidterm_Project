<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/country_phone.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farmer.php';

$idValue = $_GET['id'] ?? '';
$farmerId = is_string($idValue) ? (int) $idValue : 0;
$farmer = null;
$pageError = null;

if ($farmerId < 1) {
    http_response_code(404);
} else {
    try {
        $database = new Database();
        $farmerModel = new Farmer($database->connect());
        $farmer = $farmerModel->getById($farmerId);
        if (!$farmer) {
            http_response_code(404);
        }
    } catch (Throwable) {
        http_response_code(500);
        $pageError = 'Farmer details could not be loaded right now. Please try again later.';
    }
}

$pageTitle = 'Farmer Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Farmer Details</h1>
        <p class="mt-1 text-sm text-slate-600">View the farmer's contact and registration information.</p>
    </div>
    <a href="/FMidterm_Project/farmers/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">Back to Farmers</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php elseif (!$farmer): ?>
    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="status">
        Farmer not found.
    </div>
<?php else: ?>
    <section class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <dl class="divide-y divide-slate-100">
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Farmer ID</dt>
                <dd class="sm:col-span-2"><?= (int) $farmer['farmer_id'] ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Name</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Contact number</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars(phoneDisplayValue((string) $farmer['contact_number']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Address</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farmer['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
            <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-600">Date registered</dt>
                <dd class="sm:col-span-2"><?= htmlspecialchars($farmer['date_registered'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 px-5 py-4">
            <?php if (isAdmin()): ?>
                <a href="/FMidterm_Project/farmers/edit.php?id=<?= (int) $farmer['farmer_id'] ?>" class="rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">Edit Farmer</a>
                <a href="/FMidterm_Project/farmers/delete.php?id=<?= (int) $farmer['farmer_id'] ?>" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Delete Farmer</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
