<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$statusId = isset($_GET['status_id']) && is_string($_GET['status_id'])
    ? max(0, (int) $_GET['status_id'])
    : 0;
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'planting_id';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'DESC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$plantings = [];
$statuses = [];
$totalRows = 0;
$pageError = null;

try {
    $database = new Database();
    $plantingModel = new Planting($database->connect());
    $statuses = $plantingModel->getStatuses();
    $totalRows = $plantingModel->countAll($search, $statusId);
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $plantings = $plantingModel->getAll($search, $statusId, $sort, $direction, $perPage, ($page - 1) * $perPage);
} catch (Throwable) {
    $pageError = 'Plantings could not be loaded right now. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Plantings';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Plantings</h1>
        <p class="mt-1 text-sm text-slate-600">Track crop planting operations and expected harvest dates.</p>
    </div>
    <?php if (isAdmin()): ?>
        <a href="/FMidterm_Project/plantings/create.php" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Add Planting</a>
    <?php endif; ?>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search farm, farmer, or crop</label>
        <input type="search" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="status_id" class="mb-1 block text-sm font-medium">Filter by status</label>
        <select id="status_id" name="status_id" class="w-full rounded-md border border-slate-300 px-3 py-2">
            <option value="0">All statuses</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= (int) $status['status_id'] ?>" <?= $statusId === (int) $status['status_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/plantings/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php
                $sortableColumns = [
                    'planting_id' => 'ID',
                    'farmer_name' => 'Farmer',
                    'farm_name' => 'Farm',
                    'crop_name' => 'Crop',
                    'planting_date' => 'Planting date',
                    'area_planted' => 'Area planted',
                    'expected_harvest_date' => 'Expected harvest',
                    'status' => 'Status'
                ];
                foreach ($sortableColumns as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '/FMidterm_Project/plantings/?' . http_build_query([
                        'search' => $search,
                        'status_id' => $statusId,
                        'sort' => $column,
                        'direction' => $nextDirection
                    ]);
                ?>
                    <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">
                        <a href="<?= htmlspecialchars($sortUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="hover:text-emerald-800">
                            <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </a>
                    </th>
                <?php endforeach; ?>
                <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!$plantings): ?>
                <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No planting records found.</td></tr>
            <?php else: ?>
                <?php foreach ($plantings as $planting): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3"><?= (int) $planting['planting_id'] ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['crop_name'] . ' (' . $planting['variety'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $planting['area_planted'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['expected_harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/plantings/view.php?id=<?= (int) $planting['planting_id'] ?>">View</a>
                            <?php if (isAdmin()): ?>
                                <a class="ml-3 font-medium text-blue-800 hover:underline" href="/FMidterm_Project/plantings/edit.php?id=<?= (int) $planting['planting_id'] ?>">Edit</a>
                                <a class="ml-3 font-medium text-red-700 hover:underline" href="/FMidterm_Project/plantings/delete.php?id=<?= (int) $planting['planting_id'] ?>">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> planting record<?= $totalRows === 1 ? '' : 's' ?> found</p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'status_id' => $statusId, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'status_id' => $statusId, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
