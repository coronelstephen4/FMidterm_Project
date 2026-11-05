<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Crop.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category_id']) && is_string($_GET['category_id'])
    ? max(0, (int) $_GET['category_id'])
    : 0;
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'crop_id';
$direction = isset($_GET['direction']) && is_string($_GET['direction']) ? $_GET['direction'] : 'DESC';
$page = isset($_GET['page']) && is_string($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 10;
$crops = [];
$categories = [];
$totalRows = 0;
$pageError = null;

try {
    $database = new Database();
    $cropModel = new Crop($database->connect());
    $categories = $cropModel->getCategories();
    $totalRows = $cropModel->countAll($search, $categoryId);
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $crops = $cropModel->getAll($search, $categoryId, $sort, $direction, $perPage, ($page - 1) * $perPage);
} catch (Throwable) {
    $pageError = 'Crops could not be loaded right now. Please try again later.';
    $totalPages = 1;
}

$pageTitle = 'Crops';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Crops</h1>
        <p class="mt-1 text-sm text-slate-600">Manage crop names, varieties, and growing information.</p>
    </div>
    <?php if (isAdmin()): ?>
        <a href="/FMidterm_Project/crops/create.php" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Add Crop</a>
    <?php endif; ?>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="search" class="mb-1 block text-sm font-medium">Search crop or variety</label>
        <input type="search" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="category_id" class="mb-1 block text-sm font-medium">Filter by crop category</label>
        <select id="category_id" name="category_id" class="w-full rounded-md border border-slate-300 px-3 py-2">
            <option value="0">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['crop_category_id'] ?>" <?= $categoryId === (int) $category['crop_category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['crop_category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/crops/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <?php
                $sortableColumns = [
                    'crop_id' => 'ID',
                    'crop_name' => 'Crop',
                    'variety' => 'Variety',
                    'crop_category' => 'Category',
                    'growing_period_days' => 'Growing days',
                    'recommended_season' => 'Recommended season'
                ];
                foreach ($sortableColumns as $column => $label):
                    $nextDirection = ($sort === $column && strtoupper($direction) === 'ASC') ? 'DESC' : 'ASC';
                    $sortUrl = '/FMidterm_Project/crops/?' . http_build_query([
                        'search' => $search,
                        'category_id' => $categoryId,
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
            <?php if (!$crops): ?>
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No crops found.</td></tr>
            <?php else: ?>
                <?php foreach ($crops as $crop): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3"><?= (int) $crop['crop_id'] ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($crop['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($crop['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($crop['crop_category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= (int) $crop['growing_period_days'] ?></td>
                        <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($crop['recommended_season_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/crops/view.php?id=<?= (int) $crop['crop_id'] ?>">View</a>
                            <?php if (isAdmin()): ?>
                                <a class="ml-3 font-medium text-blue-800 hover:underline" href="/FMidterm_Project/crops/edit.php?id=<?= (int) $crop['crop_id'] ?>">Edit</a>
                                <a class="ml-3 font-medium text-red-700 hover:underline" href="/FMidterm_Project/crops/delete.php?id=<?= (int) $crop['crop_id'] ?>">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
    <p><?= $totalRows ?> crop<?= $totalRows === 1 ? '' : 's' ?> found</p>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'category_id' => $categoryId, 'sort' => $sort, 'direction' => $direction, 'page' => $page - 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="rounded-md border border-slate-300 px-3 py-2 hover:bg-white" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'category_id' => $categoryId, 'sort' => $sort, 'direction' => $direction, 'page' => $page + 1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
