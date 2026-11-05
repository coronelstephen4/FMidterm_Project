<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Crop.php';

$idValue = $_GET['id'] ?? '';
$cropId = is_string($idValue) ? (int) $idValue : 0;

if ($cropId < 1) {
    $_SESSION['error'] = 'Crop not found.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

try {
    $database = new Database();
    $cropModel = new Crop($database->connect());
    $crop = $cropModel->getById($cropId);
} catch (Throwable) {
    $_SESSION['error'] = 'Crop could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

if (!$crop) {
    $_SESSION['error'] = 'Crop not found.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cropModel->delete($cropId);
        $_SESSION['success'] = 'Crop deleted successfully.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Crop could not be deleted. A planting record may still be linked to this crop.';
    }

    header('Location: /FMidterm_Project/crops/');
    exit;
}

$pageTitle = 'Delete Crop';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Delete Crop</h1>
    <p class="mt-3 text-slate-700">
        Delete <strong><?= htmlspecialchars($crop['crop_name'] . ' — ' . $crop['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>?
        This action cannot be undone.
    </p>
    <p class="mt-2 text-sm text-slate-600">A crop with linked planting records cannot be deleted.</p>
    <form method="post" action="/FMidterm_Project/crops/delete.php?id=<?= $cropId ?>" class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Confirm Delete</button>
        <a href="/FMidterm_Project/crops/view.php?id=<?= $cropId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
