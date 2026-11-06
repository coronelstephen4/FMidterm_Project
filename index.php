<?php
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();
require_once __DIR__ . '/config/Database.php';

$summary = [
    'farmers' => 0,
    'farms' => 0,
    'crops' => 0,
    'plantings' => 0,
    'harvests' => 0
];
$recentPlantings = [];
$recentHarvests = [];
$plantingsByStatus = [];
$totalHarvested = 0.0;
$pageError = null;

try {
    $database = new Database();
    $pdo = $database->connect();

    $summarySql = "SELECT
                       (SELECT COUNT(*) FROM farmer) AS farmers,
                       (SELECT COUNT(*) FROM farm) AS farms,
                       (SELECT COUNT(*) FROM crop) AS crops,
                       (SELECT COUNT(*) FROM planting) AS plantings,
                       (SELECT COUNT(*) FROM harvest) AS harvests";
    $summary = $pdo->query($summarySql)->fetch();

    $recentPlantingsSql = "SELECT p.planting_id, p.planting_date, p.expected_harvest_date,
                                  f.farm_name, fm.first_name || ' ' || fm.last_name AS farmer_name,
                                  c.crop_name, c.variety, ps.status_name
                           FROM planting p
                           INNER JOIN farm f ON p.farm_id = f.farm_id
                           INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                           INNER JOIN crop c ON p.crop_id = c.crop_id
                           INNER JOIN planting_statuses ps ON p.status_id = ps.status_id
                           ORDER BY p.planting_date DESC, p.planting_id DESC
                           LIMIT 5";
    $recentPlantings = $pdo->query($recentPlantingsSql)->fetchAll();

    $recentHarvestsSql = "SELECT h.harvest_id, h.harvest_date, h.quantity_kg,
                                 f.farm_name, c.crop_name, c.variety,
                                 fm.first_name || ' ' || fm.last_name AS farmer_name,
                                 q.quality_grade_name, r.remark_description
                          FROM harvest h
                          INNER JOIN planting p ON h.planting_id = p.planting_id
                          INNER JOIN farm f ON p.farm_id = f.farm_id
                          INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                          INNER JOIN crop c ON p.crop_id = c.crop_id
                          INNER JOIN harvest_quality_grades q ON h.quality_grade_id = q.quality_grade_id
                          INNER JOIN harvest_remarks r ON h.remark_id = r.remark_id
                          ORDER BY h.harvest_date DESC, h.harvest_id DESC
                          LIMIT 5";
    $recentHarvests = $pdo->query($recentHarvestsSql)->fetchAll();

    $statusSql = "SELECT ps.status_name, COUNT(p.planting_id) AS planting_count
                  FROM planting_statuses ps
                  LEFT JOIN planting p ON p.status_id = ps.status_id
                  GROUP BY ps.status_id, ps.status_name
                  ORDER BY ps.status_id";
    $plantingsByStatus = $pdo->query($statusSql)->fetchAll();

    $totalHarvested = (float) $pdo->query(
        'SELECT COALESCE(SUM(quantity_kg), 0) FROM harvest'
    )->fetchColumn();
} catch (Throwable) {
    $pageError = 'Dashboard information could not be loaded right now. Please try again later.';
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div>
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">A quick overview of your agriculture records.</p>
</div>

<?php if ($pageError): ?>
    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5" aria-label="Record totals">
    <?php
    $cards = [
        ['label' => 'Farmers', 'count' => $summary['farmers'] ?? 0, 'url' => '/farmers/'],
        ['label' => 'Farms', 'count' => $summary['farms'] ?? 0, 'url' => '/farms/'],
        ['label' => 'Crops', 'count' => $summary['crops'] ?? 0, 'url' => '/crops/'],
        ['label' => 'Plantings', 'count' => $summary['plantings'] ?? 0, 'url' => '/plantings/'],
        ['label' => 'Harvests', 'count' => $summary['harvests'] ?? 0, 'url' => '/harvests/']
    ];
    foreach ($cards as $card):
    ?>
        <a href="/FMidterm_Project<?= $card['url'] ?>" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200 hover:ring-emerald-500">
            <p class="text-sm font-medium text-slate-600"><?= htmlspecialchars($card['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <p class="mt-2 text-3xl font-bold text-emerald-800"><?= (int) $card['count'] ?></p>
        </a>
    <?php endforeach; ?>
</section>

<section class="mt-6 rounded-lg bg-emerald-800 p-5 text-white shadow-sm">
    <p class="text-sm font-medium text-emerald-100">Total harvested quantity</p>
    <p class="mt-1 text-3xl font-bold"><?= number_format($totalHarvested, 2) ?> kg</p>
</section>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
    <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Recent Plantings</h2>
            <a href="/FMidterm_Project/plantings/" class="text-sm font-medium text-emerald-800 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Crop</th>
                        <th class="px-4 py-3 font-medium">Farm / Farmer</th>
                        <th class="px-4 py-3 font-medium">Planted</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!$recentPlantings): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No planting records yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentPlantings as $planting): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/plantings/view.php?id=<?= (int) $planting['planting_id'] ?>">
                                        <?= htmlspecialchars($planting['crop_name'] . ' (' . $planting['variety'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <?= htmlspecialchars($planting['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
                                    <span class="text-xs text-slate-500"><?= htmlspecialchars($planting['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($planting['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Recent Harvests</h2>
            <a href="/FMidterm_Project/harvests/" class="text-sm font-medium text-emerald-800 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Crop</th>
                        <th class="px-4 py-3 font-medium">Harvested</th>
                        <th class="px-4 py-3 font-medium">Quantity</th>
                        <th class="px-4 py-3 font-medium">Quality</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!$recentHarvests): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No harvest records yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentHarvests as $harvest): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <a class="font-medium text-emerald-800 hover:underline" href="/FMidterm_Project/harvests/view.php?id=<?= (int) $harvest['harvest_id'] ?>">
                                        <?= htmlspecialchars($harvest['crop_name'] . ' (' . $harvest['variety'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </a>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($harvest['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars($harvest['harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="whitespace-nowrap px-4 py-3"><?= htmlspecialchars((string) $harvest['quantity_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> kg</td>
                                <td class="px-4 py-3">
                                    <?= htmlspecialchars($harvest['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
                                    <span class="text-xs text-slate-500"><?= htmlspecialchars($harvest['remark_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">Plantings by Status</h2>
        <div class="mt-4 space-y-3">
            <?php if (!$plantingsByStatus): ?>
                <p class="text-sm text-slate-500">No planting statuses available.</p>
            <?php else: ?>
                <?php foreach ($plantingsByStatus as $row): ?>
                    <div class="flex items-center justify-between rounded-md bg-slate-50 px-4 py-3">
                        <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($row['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800"><?= (int) $row['planting_count'] ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
