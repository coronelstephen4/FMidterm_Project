<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Harvest.php';
require_once __DIR__ . '/../models/Lookup.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$values = [
    'planting_id' => '',
    'harvest_date' => date('Y-m-d'),
    'quantity_kg' => '',
    'selling_price_per_kg' => '',
    'quality_grade_id' => '',
    'remark_id' => ''
];
$plantings = [];
$qualityGrades = [];
$remarks = [];
$errors = [];
$warningMessages = [];
$duplicateNotice = null;
$pageError = null;

$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $harvestModel = new Harvest($pdo);
    $lookupModel = new Lookup($pdo);
    $plantings = $harvestModel->getPlantings();
    $qualityGrades = $lookupModel->getHarvestQualityGrades();
    $remarks = $lookupModel->getHarvestRemarks();
} catch (Throwable) {
    $pageError = 'Harvest form data could not be loaded right now. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    if ($pdo instanceof PDO) {
        $validated = $validator->validateHarvest($pdo, $values);
    } else {
        $validated = $values;
        $errors[] = $pageError ?? "Harvest options could not be checked right now. Please try again later.";
    }
    $errors = $validator->errors();
    $plantingId = $validated['planting_id'] ?? 0;
    $qualityGradeId = $validated['quality_grade_id'] ?? 0;
    $remarkId = $validated['remark_id'] ?? 0;

    if (!$errors && !$pageError) {
        $duplicateChecker = null;
        try {
            if (!$harvestModel->hasValidReferences($plantingId, $qualityGradeId, $remarkId)) {
                $errors[] = 'Select a valid planting, quality grade, and harvest remark.';
            } else {
                $planting = $harvestModel->getPlantingById($plantingId);
                if (!$planting) {
                    $errors[] = 'Select a valid planting record.';
                } elseif ($values['harvest_date'] < $planting['planting_date']) {
                    $errors[] = 'Harvest date cannot be before the planting date.';
                }
            }
        } catch (Throwable) {
            $errors[] = 'Harvest options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        try {
            $warningMessages = $harvestModel->getWarnings(
                $plantingId,
                $values['harvest_date'],
                $qualityGradeId,
                $remarkId
            );
        } catch (Throwable) {
            $warningMessages[] = 'Some harvest advisory checks could not be completed.';
        }
    }

    if (!$errors && !$pageError) {
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findHarvest($plantingId, $values['harvest_date']);

            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Harvest', $match);
            } else {
                $harvestId = $harvestModel->create(
                    $plantingId,
                    $values['harvest_date'],
                    $validated['quantity_kg'],
                    $validated['selling_price_per_kg'],
                    $qualityGradeId,
                    $remarkId
                );

                if ($harvestId < 1) {
                    throw new RuntimeException('Harvest record was not created.');
                }

                $_SESSION['success'] = 'Harvest record created successfully.';
                if ($warningMessages) {
                    $_SESSION['warning'] = 'Saved. Note: ' . implode(' ', $warningMessages);
                }
                header('Location: ' . APP_BASE_PATH . '/harvests/view.php?id=' . $harvestId);
                exit;
            }
        } catch (Throwable $error) {
            if (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                try {
                    if ($duplicateChecker instanceof DuplicateChecker) {
                        $match = $duplicateChecker->findHarvest($plantingId, $values['harvest_date']);
                    }
                } catch (Throwable) {
                }
                $duplicateNotice = DuplicateChecker::notice('Harvest', $match);
            } else {
                $errors[] = 'Harvest could not be saved. Please check the details and try again.';
            }
        }
    }
}

$pageTitle = 'Add Harvest';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Add Harvest</h1>
    <p class="mt-1 text-sm text-slate-600">Record yield, selling price, quality grade, and remark.</p>

    <?php if ($pageError): ?>
        <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <?= htmlspecialchars($pageError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <ul class="list-inside list-disc">
                <?php foreach (array_unique($errors) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php require __DIR__ . '/../includes/duplicate_notice.php'; ?>

    <form method="post" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div>
            <label for="planting_id" class="mb-1 block text-sm font-medium">Planting record <span class="text-rose-600" aria-hidden="true">*</span></label>
            <select id="planting_id" name="planting_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                <option value="">Select planting</option>
                <?php foreach ($plantings as $planting): ?>
                    <option value="<?= (int) $planting['planting_id'] ?>" <?= $values['planting_id'] === (string) $planting['planting_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars('#' . $planting['planting_id'] . ' — ' . $planting['crop_name'] . ' (' . $planting['variety'] . ') — ' . $planting['farm_name'] . ' — ' . $planting['farmer_name'] . ' — ' . $planting['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="harvest_date" class="mb-1 block text-sm font-medium">Harvest date <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="date" id="harvest_date" name="harvest_date" max="<?= date('Y-m-d') ?>" required value="<?= htmlspecialchars($values['harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="quantity_kg" class="mb-1 block text-sm font-medium">Quantity (kg) <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="number" id="quantity_kg" name="quantity_kg" min="0.01" max="99999999.99" step="0.01" required value="<?= htmlspecialchars($values['quantity_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="selling_price_per_kg" class="mb-1 block text-sm font-medium">Selling price per kg <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="number" id="selling_price_per_kg" name="selling_price_per_kg" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars($values['selling_price_per_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="quality_grade_id" class="mb-1 block text-sm font-medium">Quality grade <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="quality_grade_id" name="quality_grade_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select quality grade</option>
                    <?php foreach ($qualityGrades as $grade): ?>
                        <option value="<?= (int) $grade['quality_grade_id'] ?>" <?= $values['quality_grade_id'] === (string) $grade['quality_grade_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($grade['quality_grade_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label for="remark_id" class="mb-1 block text-sm font-medium">Harvest remark <span class="text-rose-600" aria-hidden="true">*</span></label>
            <select id="remark_id" name="remark_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xl">
                <option value="">Select harvest remark</option>
                <?php foreach ($remarks as $remark): ?>
                    <option value="<?= (int) $remark['remark_id'] ?>" <?= $values['remark_id'] === (string) $remark['remark_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($remark['remark_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Harvest</button>
            <a href="<?= APP_BASE_PATH ?>/harvests/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
