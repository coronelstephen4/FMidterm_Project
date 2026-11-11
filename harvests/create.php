<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Harvest.php';
require_once __DIR__ . '/../models/Lookup.php';

function isValidHarvestDate(string $date): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);
    return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}

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
$pageError = null;

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
    foreach ($values as $field => $currentValue) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $values[$field] = trim($_POST[$field]);
        }
    }

    foreach ($values as $value) {
        if ($value === '') {
            $errors[] = 'All fields are required.';
            break;
        }
    }

    $plantingId = ctype_digit($values['planting_id']) ? (int) $values['planting_id'] : 0;
    $qualityGradeId = ctype_digit($values['quality_grade_id']) ? (int) $values['quality_grade_id'] : 0;
    $remarkId = ctype_digit($values['remark_id']) ? (int) $values['remark_id'] : 0;
    $amountPattern = '/^\d{1,8}(\.\d{1,2})?$/';
    $quantityValid = preg_match($amountPattern, $values['quantity_kg']) && (float) $values['quantity_kg'] > 0;
    $priceValid = preg_match($amountPattern, $values['selling_price_per_kg']) && (float) $values['selling_price_per_kg'] >= 0;

    if (!$quantityValid) {
        $errors[] = 'Quantity must be positive with up to two decimal places.';
    }
    if (!$priceValid) {
        $errors[] = 'Selling price must be zero or greater with up to two decimal places.';
    }
    if (!isValidHarvestDate($values['harvest_date'])) {
        $errors[] = 'Enter a valid harvest date.';
    }
    if ($plantingId < 1 || $qualityGradeId < 1 || $remarkId < 1) {
        $errors[] = 'Select a valid planting, quality grade, and harvest remark.';
    }

    if (!$errors && !$pageError) {
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
            if ($harvestModel->hasDuplicate(
                $plantingId,
                $values['harvest_date'],
                (float) $values['quantity_kg'],
                (float) $values['selling_price_per_kg'],
                $qualityGradeId,
                $remarkId
            )) {
                $errors[] = 'A harvest with the same planting, date, quantity, price, grade, and remark already exists.';
            }
        } catch (Throwable) {
            $errors[] = 'Harvest records could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        try {
            $harvestId = $harvestModel->create(
                $plantingId,
                $values['harvest_date'],
                (float) $values['quantity_kg'],
                (float) $values['selling_price_per_kg'],
                $qualityGradeId,
                $remarkId
            );

            if ($harvestId < 1) {
                throw new RuntimeException('Harvest record was not created.');
            }

            $_SESSION['success'] = 'Harvest record created successfully.';
            header('Location: /FMidterm_Project/harvests/view.php?id=' . $harvestId);
            exit;
        } catch (Throwable) {
            $errors[] = 'Harvest record could not be saved. Please check the details and try again.';
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

    <form method="post" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div>
            <label for="planting_id" class="mb-1 block text-sm font-medium">Planting record</label>
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
                <label for="harvest_date" class="mb-1 block text-sm font-medium">Harvest date</label>
                <input type="date" id="harvest_date" name="harvest_date" required value="<?= htmlspecialchars($values['harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="quantity_kg" class="mb-1 block text-sm font-medium">Quantity (kg)</label>
                <input type="number" id="quantity_kg" name="quantity_kg" min="0.01" max="99999999.99" step="0.01" required value="<?= htmlspecialchars($values['quantity_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="selling_price_per_kg" class="mb-1 block text-sm font-medium">Selling price per kg</label>
                <input type="number" id="selling_price_per_kg" name="selling_price_per_kg" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars($values['selling_price_per_kg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="quality_grade_id" class="mb-1 block text-sm font-medium">Quality grade</label>
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
            <label for="remark_id" class="mb-1 block text-sm font-medium">Harvest remark</label>
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
            <a href="/FMidterm_Project/harvests/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
