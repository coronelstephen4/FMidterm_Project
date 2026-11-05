<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';

$idValue = $_GET['id'] ?? '';
$farmId = is_string($idValue) ? (int) $idValue : 0;

if ($farmId < 1) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: /FMidterm_Project/farms/');
    exit;
}

try {
    $database = new Database();
    $farmModel = new Farm($database->connect());
    $farm = $farmModel->getById($farmId);
} catch (Throwable) {
    $_SESSION['error'] = 'Farm could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/farms/');
    exit;
}

if (!$farm) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: /FMidterm_Project/farms/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $farmModel->delete($farmId);
        $_SESSION['success'] = 'Farm deleted successfully.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Farm could not be deleted. A planting record may still be linked to this farm.';
    }

    header('Location: /FMidterm_Project/farms/');
    exit;
}

$pageTitle = 'Delete Farm';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Delete Farm</h1>
    <p class="mt-3 text-slate-700">
        Delete <strong><?= htmlspecialchars($farm['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>?
        This action cannot be undone.
    </p>
    <p class="mt-2 text-sm text-slate-600">A farm with linked planting records cannot be deleted.</p>
    <form method="post" action="/FMidterm_Project/farms/delete.php?id=<?= $farmId ?>" class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Confirm Delete</button>
        <a href="/FMidterm_Project/farms/view.php?id=<?= $farmId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
