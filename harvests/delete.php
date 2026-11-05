<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Harvest.php';

$idValue = $_GET['id'] ?? '';
$harvestId = is_string($idValue) ? (int) $idValue : 0;

if ($harvestId < 1) {
    $_SESSION['error'] = 'Harvest record not found.';
    header('Location: /FMidterm_Project/harvests/');
    exit;
}

try {
    $database = new Database();
    $harvestModel = new Harvest($database->connect());
    $harvest = $harvestModel->getById($harvestId);
} catch (Throwable) {
    $_SESSION['error'] = 'Harvest record could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/harvests/');
    exit;
}

if (!$harvest) {
    $_SESSION['error'] = 'Harvest record not found.';
    header('Location: /FMidterm_Project/harvests/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $harvestModel->delete($harvestId);
        $_SESSION['success'] = 'Harvest record deleted successfully.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Harvest record could not be deleted. Please try again.';
    }

    header('Location: /FMidterm_Project/harvests/');
    exit;
}

$pageTitle = 'Delete Harvest';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Delete Harvest</h1>
    <p class="mt-3 text-slate-700">
        Delete harvest record <strong>#<?= (int) $harvest['harvest_id'] ?></strong> for
        <strong><?= htmlspecialchars($harvest['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>?
        This action cannot be undone.
    </p>
    <form method="post" action="/FMidterm_Project/harvests/delete.php?id=<?= $harvestId ?>" class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Confirm Delete</button>
        <a href="/FMidterm_Project/harvests/view.php?id=<?= $harvestId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
