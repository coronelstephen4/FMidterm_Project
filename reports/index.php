<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold text-slate-900">Reports</h1>
<p class="mt-1 text-sm text-slate-600">View summaries generated from the AIS database.</p>

<div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Farmers and their farms</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">Browse farmer details and any farms registered to them.</p>
        <a href="/FMidterm_Project/reports/farmers_farms.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Crops planted per farm</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">Review planting records by farm, crop, and planting date.</p>
        <a href="/FMidterm_Project/reports/crops_per_farm.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Plantings by status</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">View planting totals by status and filter the planting records.</p>
        <a href="/FMidterm_Project/reports/plantings_by_status.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Harvest records</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">Review harvest yields with related farmer, farm, crop, quality, and remark details.</p>
        <a href="/FMidterm_Project/reports/harvest_records.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Total harvested quantity</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">See harvested kilograms across a selected date range, with a quality breakdown.</p>
        <a href="/FMidterm_Project/reports/total_harvested_quantity.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
    <section class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <h2 class="font-semibold text-slate-900 sm:text-lg">Estimated harvest value</h2>
        <p class="mt-1 text-xs text-slate-600 sm:text-sm">Estimate value using harvested kilograms multiplied by selling price per kilogram.</p>
        <a href="/FMidterm_Project/reports/estimated_harvest_value.php" class="mt-3 inline-flex rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 sm:mt-4 sm:px-4">Open report</a>
    </section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
