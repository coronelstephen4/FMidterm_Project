<?php
$duplicateRecordType = (string) ($duplicateNotice['type'] ?? '');
$duplicateRecordId = (int) ($duplicateNotice['id'] ?? 0);
$duplicateMessage = (string) ($duplicateNotice['message'] ?? '');
$duplicateRoutes = [
    'Farmer' => 'farmers',
    'Farm' => 'farms',
    'Planting' => 'plantings',
    'Harvest' => 'harvests',
    'Crop variety' => 'crops'
];
$duplicateRoute = $duplicateRoutes[$duplicateRecordType] ?? null;
?>
<?php if ($duplicateMessage !== ''): ?>
    <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" role="alert">
        <p><?= htmlspecialchars($duplicateMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php if ($duplicateRoute && !empty($duplicateRecordId)): ?>
            <div class="mt-2 flex gap-4 text-sm font-semibold">
                <a class="underline" href="<?= APP_BASE_PATH ?>/<?= $duplicateRoute ?>/view.php?id=<?= (int) $duplicateRecordId ?>">View record</a>
                <a class="underline" href="<?= APP_BASE_PATH ?>/<?= $duplicateRoute ?>/edit.php?id=<?= (int) $duplicateRecordId ?>">Edit record</a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
