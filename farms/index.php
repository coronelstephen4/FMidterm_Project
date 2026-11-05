<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$irrigationTypeId = isset($_GET['irrigation_type_id']) && is_string($_GET['irrigation_type_id'])
    ? max(0, (int) $_GET['irrigation_type_id'])
    : 0;
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'farm_id';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'DESC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$farms = [];
$irrigationTypes = [];
$totalRows = 0;
$pageError = null;

try {
    $database = new Database();
    $farmModel = new Farm($database->connect());
    $irrigationTypes = $farmModel->getIrrigationTypes();
    $totalRows = $farmModel->countAll($search, $irrigationTypeId);
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $farms = $farmModel->getAll(
        $search,
        $irrigationTypeId,
        $sort,
        $direction,
        $perPage,
        ($page - 1) * $perPage
    );
} catch (Throwable) {
    $pageError = 'Farms could not be loaded right now. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Farms';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Farms</h1>
        <p class="mt-1 text-sm text-slate-600">Manage farms and their related farmer information.</p>
    </div>
    <?php if (isAdmin()): ?>
        <a href="/FMidterm_Project/farms/create.php" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Add Farm</a>
    <?php endif; ?>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search farm, location, or farmer</label>
        <input type="search" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="irrigation_type_id" class="mb-1 block text-sm font-medium">Filter by irrigation type</label>
        <select id="irrigation_type_id" name="irrigation_type_id" class="w-full rounded-md border border-slate-300 px-3 py-2">
            <option value="0">All irrigation types</option>
            <?php foreach ($irrigationTypes as $type): ?>
                <option value="<?= (int) $type['irrigation_type_id'] ?>" <?= $irrigationTypeId === (int) $type['irrigation_type_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type['irrigation_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/farms/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php
                $sortableColumns = [
                    'farm_id' => 'ID',
                    'farm_name' => 'Farm',
                    'farmer_name' => 'Farmer',
                    'location' => 'Location',
                    'area_hectares' => 'Area (ha)',
                    'irrigation_type' => 'Irrigation'
                ];
                foreach ($sortableColumns as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '/FMidterm_Project/farms/?' . http_build_query([
                        'search' => $search,
                        'irrigation_type_id' => $irrigationTypeId,
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
                <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">Soil</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!$farms): ?>
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No farms found.</td></tr>
            <?php else: ?>
                <?php foreach ($farms as $farm): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3"><?= (int) $farm['farm_id'] ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($farm['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($farm['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($farm['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $farm['area_hectares'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($farm['irrigation_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($farm['soil_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/farms/view.php?id=<?= (int) $farm['farm_id'] ?>">View</a>
                            <?php if (isAdmin()): ?>
                                <a class="ml-3 font-medium text-blue-800 hover:underline" href="/FMidterm_Project/farms/edit.php?id=<?= (int) $farm['farm_id'] ?>">Edit</a>
                                <a class="ml-3 font-medium text-red-700 hover:underline" href="/FMidterm_Project/farms/delete.php?id=<?= (int) $farm['farm_id'] ?>">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> farm<?= $totalRows === 1 ? '' : 's' ?> found</p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'irrigation_type_id' => $irrigationTypeId, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'irrigation_type_id' => $irrigationTypeId, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
