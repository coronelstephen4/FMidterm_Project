<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/Timezone.php';
requireLogin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Report.php';

$dateFrom = isset($_GET['date_from']) && is_string($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) && is_string($_GET['date_to']) ? trim($_GET['date_to']) : '';
$autoPrint = isset($_GET['print_all'], $_GET['auto_print']) && $_GET['print_all'] === '1' && $_GET['auto_print'] === '1';
$summary = ['harvest_count' => 0, 'total_quantity_kg' => 0];
$qualityTotals = [];
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

if ($dateError === null) {
    try {
        $report = new Report((new Database())->connect());
        $summary = $report->getTotalHarvestQuantity($dateFrom, $dateTo);
        $qualityTotals = $report->getHarvestQuantityByQuality($dateFrom, $dateTo);
    } catch (Throwable) {
        $pageError = 'The Total Harvested Quantity report could not be loaded. Please try again later.';
    }
}

$printAllUrl = $pageError === null && $dateError === null ? '?' . http_build_query([
    'date_from' => $dateFrom, 'date_to' => $dateTo,
    'print_all' => '1', 'auto_print' => '1'
]) : '';
$pageTitle = 'Total harvested quantity';
$printOrientation = 'landscape';
$reportPrint = true;
$reportPrintGenerated = date('Y-m-d H:i');
$reportPrintRecordCount = (int) $summary['harvest_count'];
$reportPrintNoRecords = $reportPrintRecordCount === 0 && $pageError === null && $dateError === null;
$reportPrintFilters = ['Date from' => $dateFrom, 'Date to' => $dateTo];
$reportPrintTotals = ['Total quantity (kg)' => number_format((float) $summary['total_quantity_kg'], 2)];
require __DIR__ . '/../includes/header.php';
$reportPrintSection = 'header';
require __DIR__ . '/../includes/report_print.php';
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Total harvested quantity</h1>
        <p class="mt-1 text-sm text-slate-600">Quantities are summed from harvest records in the selected date range.</p>
    </div>
    <?php require __DIR__ . '/../includes/report_actions.php'; ?>
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

<form method="get" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3">
    <div>
        <label for="date_from" class="mb-1 block text-sm font-medium">Harvest date from</label>
        <input id="date_from" name="date_from" type="date" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label for="date_to" class="mb-1 block text-sm font-medium">Harvest date to</label>
        <input id="date_to" name="date_to" type="date" value="<?= htmlspecialchars($dateTo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Apply</button>
        <a href="/FMidterm_Project/reports/total_harvested_quantity.php" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Clear</a>
    </div>
</form>

<section class="mt-6 grid gap-4 sm:grid-cols-2" aria-label="Harvest totals">
    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-medium text-slate-600">Total quantity</p>
        <p class="mt-2 text-3xl font-bold text-emerald-800"><?= htmlspecialchars(number_format((float) $summary['total_quantity_kg'], 2), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> kg</p>
    </div>
    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-medium text-slate-600">Harvest records</p>
        <p class="mt-2 text-3xl font-bold text-slate-900"><?= (int) $summary['harvest_count'] ?></p>
    </div>
</section>

<section class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200" aria-labelledby="quality-breakdown-heading">
    <h2 id="quality-breakdown-heading" class="px-4 pt-4 text-lg font-semibold text-slate-900">Quantity by quality grade</h2>
    <table class="print-total-quantity mt-3 min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 text-left text-slate-700">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Quality grade</th>
                <th scope="col" class="px-4 py-3 font-semibold">Harvest records</th>
                <th scope="col" class="px-4 py-3 font-semibold">Quantity (kg)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!$qualityTotals): ?>
                <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">No quality grades are configured.</td></tr>
            <?php else: foreach ($qualityTotals as $row): ?>
                <tr>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><?= (int) $row['harvest_count'] ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars(number_format((float) $row['total_quantity_kg'], 2), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</section>
<?php $reportPrintSection = 'summary'; require __DIR__ . '/../includes/report_print.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
