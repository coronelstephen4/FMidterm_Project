<?php
$printEscape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
if (($reportPrintSection ?? '') === 'header'):
?>
<section class="print-only print-report-header" aria-label="Print report details">
    <strong>Agriculture Information System</strong>
    <h1><?= $printEscape($pageTitle ?? 'Report') ?></h1>
    <p>Generated: <?= $printEscape($reportPrintGenerated ?? date('Y-m-d H:i')) ?> | User: <?= $printEscape($_SESSION['username'] ?? 'Unknown') ?> (<?= $printEscape(($_SESSION['role'] ?? '') === 'admin' ? 'Administrator' : 'Staff') ?>)</p>
    <p>Filters: <?php
        $filterText = [];
        foreach (($reportPrintFilters ?? []) as $label => $value) {
            $filterText[] = $label . ': ' . (($value === '' || $value === null) ? 'None' : (string) $value);
        }
        echo $printEscape($filterText ? implode('; ', $filterText) : 'None');
    ?></p>
</section>
<?php if (!empty($reportPrintNoRecords)): ?>
    <p class="print-only print-no-records">No records found for the selected filters.</p>
<?php endif; ?>
<?php elseif (($reportPrintSection ?? '') === 'summary'): ?>
<section class="print-only print-summary" aria-label="Report totals">
    <h2>Report summary</h2>
    <p><strong>Total records:</strong> <?= (int) ($reportPrintRecordCount ?? 0) ?></p>
    <?php foreach (($reportPrintTotals ?? []) as $label => $value): ?>
        <p><strong><?= $printEscape($label) ?>:</strong> <?= $printEscape($value) ?></p>
    <?php endforeach; ?>
    <p><strong>End of report</strong></p>
</section>
<?php endif; ?>
