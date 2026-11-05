<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farmer.php';

$idValue = $_GET['id'] ?? '';
$farmerId = is_string($idValue) ? (int) $idValue : 0;

if ($farmerId < 1) {
    $_SESSION['error'] = 'Farmer not found.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

try {
    $database = new Database();
    $farmerModel = new Farmer($database->connect());
    $farmer = $farmerModel->getById($farmerId);
} catch (Throwable) {
    $_SESSION['error'] = 'Farmer could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

if (!$farmer) {
    $_SESSION['error'] = 'Farmer not found.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $farmerModel->delete($farmerId);
        $_SESSION['success'] = 'Farmer deleted successfully.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Farmer could not be deleted. A farm may still be linked to this farmer.';
    }

    header('Location: /FMidterm_Project/farmers/');
    exit;
}

$pageTitle = 'Delete Farmer';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Delete Farmer</h1>
    <p class="mt-3 text-slate-700">
        Delete <strong><?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>?
        This action cannot be undone.
    </p>
    <p class="mt-2 text-sm text-slate-600">A farmer with linked farms cannot be deleted.</p>
    <form method="post" action="/FMidterm_Project/farmers/delete.php?id=<?= $farmerId ?>" class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800">Confirm Delete</button>
        <a href="/FMidterm_Project/farmers/view.php?id=<?= $farmerId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
