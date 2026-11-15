<?php
$printEscape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
if (($reportPrintSection ?? '') === 'page_start'):
?>
<table class="print-page-frame" role="presentation">
    <thead><tr><td></td></tr></thead>
    <tfoot><tr><td><div class="print-page-footer print-only"><?= $printEscape(APP_NAME) ?> | Generated: <?= $printEscape($reportPrintGenerated ?? date('Y-m-d H:i')) ?></div></td></tr></tfoot>
    <tbody><tr><td class="print-page-content">
<?php elseif (($reportPrintSection ?? '') === 'page_end'): ?>
    </td></tr></tbody>
</table>
<?php elseif (($reportPrintSection ?? '') === 'header'):
    $filterText = [];
    foreach (($reportPrintFilters ?? []) as $label => $value) {
        if ($value === '' || $value === null || in_array($value, ['All farms', 'All statuses', 'All quality grades'], true) || $label === 'Sort') {
            continue;
        }
        $filterText[] = $label . ': ' . (string) $value;
    }
?>
<?php if ($filterText): ?>
    <p class="print-only print-report-filters"><?= $printEscape(implode(' | ', $filterText)) ?></p>
<?php endif; ?>
<?php if (!empty($reportPrintNoRecords)): ?>
    <p class="print-only print-no-records">No records found for the selected filters.</p>
<?php endif; ?>
<?php endif; ?>
