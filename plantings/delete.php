<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';

$idValue = $_GET['id'] ?? '';
$plantingId = is_string($idValue) ? (int) $idValue : 0;

if ($plantingId < 1) {
    $_SESSION['error'] = 'Planting record not found.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

try {
    $database = new Database();
    $plantingModel = new Planting($database->connect());
    $planting = $plantingModel->getById($plantingId);
} catch (Throwable) {
    $_SESSION['error'] = 'Planting record could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

if (!$planting) {
    $_SESSION['error'] = 'Planting record not found.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $plantingModel->delete($plantingId);
        $_SESSION['success'] = 'Planting record deleted successfully.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Planting record could not be deleted. A harvest record may still be linked to it.';
    }

    header('Location: /FMidterm_Project/plantings/');
    exit;
}

$pageTitle = 'Delete Planting';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Delete Planting</h1>
    <p class="mt-3 text-slate-700">
        Delete planting record <strong>#<?= (int) $planting['planting_id'] ?></strong> for
        <strong><?= htmlspecialchars($planting['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>?
        This action cannot be undone.
    </p>
    <p class="mt-2 text-sm text-slate-600">A planting record with linked harvests cannot be deleted.</p>
    <form method="post" action="/FMidterm_Project/plantings/delete.php?id=<?= $plantingId ?>" class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Confirm Delete</button>
        <a href="/FMidterm_Project/plantings/view.php?id=<?= $plantingId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
