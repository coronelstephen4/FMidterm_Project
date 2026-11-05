<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold text-slate-900">Reports</h1>
<p class="mt-1 text-sm text-slate-600">View summaries generated from the AIS database.</p>

<div class="mt-6 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Farmers and their farms</h2>
    <p class="mt-1 text-sm text-slate-600">Browse farmer details and any farms registered to them.</p>
    <a href="/FMidterm_Project/reports/farmers_farms.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<div class="mt-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Crops planted per farm</h2>
    <p class="mt-1 text-sm text-slate-600">Review planting records by farm, crop, and planting date.</p>
    <a href="/FMidterm_Project/reports/crops_per_farm.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<div class="mt-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Plantings by status</h2>
    <p class="mt-1 text-sm text-slate-600">View planting totals by status and filter the planting records.</p>
    <a href="/FMidterm_Project/reports/plantings_by_status.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<div class="mt-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Harvest records</h2>
    <p class="mt-1 text-sm text-slate-600">Review harvest yields with related farmer, farm, crop, quality, and remark details.</p>
    <a href="/FMidterm_Project/reports/harvest_records.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<div class="mt-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Total harvested quantity</h2>
    <p class="mt-1 text-sm text-slate-600">See harvested kilograms across a selected date range, with a quality breakdown.</p>
    <a href="/FMidterm_Project/reports/total_harvested_quantity.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<div class="mt-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold text-slate-900">Estimated harvest value</h2>
    <p class="mt-1 text-sm text-slate-600">Estimate value using harvested kilograms multiplied by selling price per kilogram.</p>
    <a href="/FMidterm_Project/reports/estimated_harvest_value.php" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Open report</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
