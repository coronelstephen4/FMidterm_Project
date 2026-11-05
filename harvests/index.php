<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Harvest.php';
require_once __DIR__ . '/../models/Lookup.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$qualityGradeId = isset($_GET['quality_grade_id']) && is_string($_GET['quality_grade_id'])
    ? max(0, (int) $_GET['quality_grade_id'])
    : 0;
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'harvest_id';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'DESC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$harvests = [];
$qualityGrades = [];
$totalRows = 0;
$pageError = null;

try {
    $database = new Database();
    $pdo = $database->connect();
    $harvestModel = new Harvest($pdo);
    $lookupModel = new Lookup($pdo);
    $qualityGrades = $lookupModel->getHarvestQualityGrades();
    $totalRows = $harvestModel->countAll($search, $qualityGradeId);
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $harvests = $harvestModel->getAll($search, $qualityGradeId, $sort, $direction, $perPage, ($page - 1) * $perPage);
} catch (Throwable) {
    $pageError = 'Harvest records could not be loaded right now. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Harvests';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Harvests</h1>
        <p class="mt-1 text-sm text-slate-600">Record harvest yields, quality, and remarks.</p>
    </div>
    <?php if (isAdmin()): ?>
        <a href="/FMidterm_Project/harvests/create.php" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Add Harvest</a>
    <?php endif; ?>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search farmer, farm, crop, or remark</label>
        <input type="search" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="quality_grade_id" class="mb-1 block text-sm font-medium">Filter by quality grade</label>
        <select id="quality_grade_id" name="quality_grade_id" class="w-full rounded-md border border-slate-300 px-3 py-2">
            <option value="0">All quality grades</option>
            <?php foreach ($qualityGrades as $grade): ?>
                <option value="<?= (int) $grade['quality_grade_id'] ?>" <?= $qualityGradeId === (int) $grade['quality_grade_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($grade['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/harvests/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php
                $sortableColumns = [
                    'harvest_id' => 'ID',
                    'farmer_name' => 'Farmer',
                    'farm_name' => 'Farm',
                    'crop_name' => 'Crop',
                    'harvest_date' => 'Harvest date',
                    'quantity_kg' => 'Quantity (kg)',
                    'selling_price_per_kg' => 'Price per kg',
                    'quality_grade' => 'Quality',
                    'remark' => 'Remark'
                ];
                foreach ($sortableColumns as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '/FMidterm_Project/harvests/?' . http_build_query([
                        'search' => $search,
                        'quality_grade_id' => $qualityGradeId,
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
            <?php if (!$harvests): ?>
                <tr><td colspan="10" class="px-4 py-8 text-center text-slate-500">No harvest records found.</td></tr>
            <?php else: ?>
                <?php foreach ($harvests as $harvest): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3"><?= (int) $harvest['harvest_id'] ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['crop_name'] . ' (' . $harvest['variety'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $harvest['quantity_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $harvest['selling_price_per_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['remark_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/harvests/view.php?id=<?= (int) $harvest['harvest_id'] ?>">View</a>
                            <?php if (isAdmin()): ?>
                                <a class="ml-3 font-medium text-blue-800 hover:underline" href="/FMidterm_Project/harvests/edit.php?id=<?= (int) $harvest['harvest_id'] ?>">Edit</a>
                                <a class="ml-3 font-medium text-red-700 hover:underline" href="/FMidterm_Project/harvests/delete.php?id=<?= (int) $harvest['harvest_id'] ?>">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> harvest record<?= $totalRows === 1 ? '' : 's' ?> found</p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'quality_grade_id' => $qualityGradeId, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'quality_grade_id' => $qualityGradeId, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
