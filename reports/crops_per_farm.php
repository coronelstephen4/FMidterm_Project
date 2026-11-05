<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Report.php';

$farmId = isset($_GET['farm_id']) && is_string($_GET['farm_id']) ? max(0, (int) $_GET['farm_id']) : 0;
$dateFrom = isset($_GET['date_from']) && is_string($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) && is_string($_GET['date_to']) ? trim($_GET['date_to']) : '';
$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'farm_name';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'ASC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$farms = [];
$rows = [];
$totalRows = 0;
$pageError = null;
$dateError = null;
$validDate = static function (string $value): bool {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
};

if (($dateFrom !== '' && !$validDate($dateFrom)) || ($dateTo !== '' && !$validDate($dateTo))) {
    $dateError = 'Enter valid dates using the year-month-day format.';
} elseif ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    $dateError = 'The start date must be before or equal to the end date.';
}

try {
    $report = new Report((new Database())->connect());
    $farms = $report->getFarmOptions();
    if ($dateError === null) {
        $totalRows = $report->countCropsPlantedPerFarm($farmId, $dateFrom, $dateTo, $search);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $rows = $report->getCropsPlantedPerFarm(
            $farmId, $dateFrom, $dateTo, $search, $sort, $direction, $perPage, ($page - 1) * $perPage
        );
    } else {
        $totalPages = 1;
    }
} catch (Throwable) {
    $pageError = 'The Crops Planted per Farm report could not be loaded. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Crops Planted per Farm Report';
require __DIR__ . '/../includes/header.php';
$sortLabels = [
    'farm_name' => 'Farm', 'farmer_name' => 'Farmer', 'crop_name' => 'Crop',
    'variety' => 'Variety', 'planting_date' => 'Planting date', 'area_planted' => 'Area planted', 'status' => 'Status'
];
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Crops planted per farm</h1>
        <p class="mt-1 text-sm text-slate-600">Filter planting records by farm and planting date.</p>
    </div>
    <a href="/FMidterm_Project/reports/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">All reports</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if ($dateError): ?>
    <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="alert">
        <?= htmlspecialchars($dateError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">
    <div>
        <label for="farm_id" class="mb-1 block text-sm font-medium">Farm</label>
        <select id="farm_id" name="farm_id" class="w-full rounded-md border border-slate-300 px-3 py-2">
            <option value="0">All farms</option>
            <?php foreach ($farms as $farm): ?>
                <option value="<?= (int) $farm['farm_id'] ?>" <?= $farmId === (int) $farm['farm_id'] ? 'selected' : '' ?>><?= htmlspecialchars($farm['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="date_from" class="mb-1 block text-sm font-medium">Planting date from</label>
        <input id="date_from" name="date_from" type="date" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="date_to" class="mb-1 block text-sm font-medium">Planting date to</label>
        <input id="date_to" name="date_to" type="date" value="<?= htmlspecialchars($dateTo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search farm, farmer, or crop</label>
        <input id="search" name="search" type="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/reports/crops_per_farm.php" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php foreach ($sortLabels as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '?' . http_build_query([
                        'farm_id' => $farmId, 'date_from' => $dateFrom, 'date_to' => $dateTo,
                        'search' => $search, 'sort' => $column, 'direction' => $nextDirection
                    ]);
                ?>
                    <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold"><a class="hover:text-emerald-800" href="<?= htmlspecialchars($sortUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a></th>
                <?php endforeach; ?>
                <th class="whitespace-nowrap px-4 py-3 font-semibold">Farm location</th>
                <th class="whitespace-nowrap px-4 py-3 font-semibold">Expected harvest</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No planting records match these filters.</td></tr>
            <?php else: foreach ($rows as $row): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3"><?= htmlspecialchars($row['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($row['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $row['area_planted'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['status_name'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($row['expected_harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> planting record<?= $totalRows === 1 ? '' : 's' ?></p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?><a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['farm_id' => $farmId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a><?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?><a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['farm_id' => $farmId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
