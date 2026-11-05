<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Report.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$address = isset($_GET['address']) && is_string($_GET['address']) ? trim($_GET['address']) : '';
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'farmer_id';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'ASC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$rows = [];
$totalRows = 0;
$pageError = null;

try {
    $report = new Report((new Database())->connect());
    $totalRows = $report->countFarmersAndFarms($search, $address);
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $rows = $report->getFarmersAndFarms(
        $search, $address, $sort, $direction, $perPage, ($page - 1) * $perPage
    );
} catch (Throwable) {
    $pageError = 'The Farmers and Farms report could not be loaded. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Farmers and Farms Report';
require __DIR__ . '/../includes/header.php';
$sortLabels = [
    'farmer_id' => 'Farmer ID', 'farmer_name' => 'Farmer', 'address' => 'Address',
    'farm_name' => 'Farm', 'location' => 'Farm location', 'area_hectares' => 'Area (ha)'
];
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Farmers and their farms</h1>
        <p class="mt-1 text-sm text-slate-600">Farmers without a registered farm are included.</p>
    </div>
    <a href="/FMidterm_Project/reports/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">All reports</a>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search farmer, farm, contact, or location</label>
        <input id="search" name="search" type="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="address" class="mb-1 block text-sm font-medium">Filter by farmer address</label>
        <input id="address" name="address" type="search" value="<?= htmlspecialchars($address, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div class="flex items-end gap-2">
        <button class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900" type="submit">Apply</button>
        <a href="/FMidterm_Project/reports/farmers_farms.php" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php foreach ($sortLabels as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '?' . http_build_query(['search' => $search, 'address' => $address, 'sort' => $column, 'direction' => $nextDirection]);
                ?>
                    <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold"><a class="hover:text-emerald-800" href="<?= htmlspecialchars($sortUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a></th>
                <?php endforeach; ?>
                <th class="whitespace-nowrap px-4 py-3 font-semibold">Contact</th>
                <th class="whitespace-nowrap px-4 py-3 font-semibold">Soil</th>
                <th class="whitespace-nowrap px-4 py-3 font-semibold">Irrigation</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!$rows): ?>
                <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No matching farmers or farms found.</td></tr>
            <?php else: foreach ($rows as $row): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3"><?= (int) $row['farmer_id'] ?></td>
                    <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['farm_name'] ?? 'No farm registered', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['location'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= $row['area_hectares'] === null ? '—' : htmlspecialchars((string) $row['area_hectares'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['contact_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['soil_type_name'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['irrigation_type_name'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> result<?= $totalRows === 1 ? '' : 's' ?></p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?><a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'address' => $address, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a><?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?><a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'address' => $address, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
