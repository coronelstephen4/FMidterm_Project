<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';

function isValidPlantingDate(string $date): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);
    return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}

$today = date('Y-m-d');
$futureDate = date('Y-m-d', strtotime('+30 days'));
$values = [
    'farm_id' => '',
    'crop_id' => '',
    'planting_date' => $today,
    'area_planted' => '',
    'seed_quantity' => '',
    'expected_harvest_date' => $futureDate,
    'status_id' => '2'
];
$farms = [];
$crops = [];
$statuses = [];
$errors = [];
$pageError = null;

try {
    $database = new Database();
    $plantingModel = new Planting($database->connect());
    $farms = $plantingModel->getFarms();
    $crops = $plantingModel->getCrops();
    $statuses = $plantingModel->getStatuses();
} catch (Throwable) {
    $pageError = 'Planting form data could not be loaded right now. Please try again later.';
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

    $farmId = ctype_digit($values['farm_id']) ? (int) $values['farm_id'] : 0;
    $cropId = ctype_digit($values['crop_id']) ? (int) $values['crop_id'] : 0;
    $statusId = ctype_digit($values['status_id']) ? (int) $values['status_id'] : 0;
    $areaPattern = '/^\d{1,6}(\.\d{1,2})?$/';
    $areaValid = preg_match($areaPattern, $values['area_planted']) && (float) $values['area_planted'] > 0;
    $seedValid = preg_match($areaPattern, $values['seed_quantity']) && (float) $values['seed_quantity'] > 0;

    if (!$areaValid) {
        $errors[] = 'Area planted must be positive with up to two decimal places.';
    }
    if (!$seedValid) {
        $errors[] = 'Seed quantity must be positive with up to two decimal places.';
    }
    if (!isValidPlantingDate($values['planting_date']) || !isValidPlantingDate($values['expected_harvest_date'])) {
        $errors[] = 'Enter valid planting and expected harvest dates.';
    } elseif ($values['expected_harvest_date'] <= $values['planting_date']) {
        $errors[] = 'Expected harvest date must be after the planting date.';
    }
    if ($farmId < 1 || $cropId < 1 || $statusId < 1) {
        $errors[] = 'Select a valid farm, crop, and planting status.';
    }

    if (!$errors && !$pageError) {
        try {
            if (!$plantingModel->hasValidReferences($farmId, $cropId, $statusId)) {
                $errors[] = 'Select a valid farm, crop, and planting status.';
            }
        } catch (Throwable) {
            $errors[] = 'Planting options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        try {
            if ($plantingModel->hasDuplicate(
                $farmId,
                $cropId,
                $values['planting_date'],
                (float) $values['area_planted'],
                (float) $values['seed_quantity'],
                $values['expected_harvest_date'],
                $statusId
            )) {
                $errors[] = 'A planting with the same farm, crop, dates, quantities, and status already exists.';
            }
        } catch (Throwable) {
            $errors[] = 'Planting records could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        try {
            $plantingId = $plantingModel->create(
                $farmId,
                $cropId,
                $values['planting_date'],
                (float) $values['area_planted'],
                (float) $values['seed_quantity'],
                $values['expected_harvest_date'],
                $statusId
            );

            if ($plantingId < 1) {
                throw new RuntimeException('Planting was not created.');
            }

            $_SESSION['success'] = 'Planting record created successfully.';
            header('Location: /FMidterm_Project/plantings/view.php?id=' . $plantingId);
            exit;
        } catch (Throwable $error) {
            $errors[] = isDuplicateDatabaseError($error)
                ? 'A planting with the same farm, crop, dates, quantities, and status already exists.'
                : 'Planting could not be saved. Please check the details and try again.';
        }
    }
}

$pageTitle = 'Add Planting';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Add Planting</h1>
    <p class="mt-1 text-sm text-slate-600">Record the farm, crop, dates, quantities, and status.</p>

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
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="farm_id" class="mb-1 block text-sm font-medium">Farm</label>
                <select id="farm_id" name="farm_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int) $farm['farm_id'] ?>" <?= $values['farm_id'] === (string) $farm['farm_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($farm['farm_name'] . ' — ' . $farm['farmer_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="crop_id" class="mb-1 block text-sm font-medium">Crop</label>
                <select id="crop_id" name="crop_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select crop</option>
                    <?php foreach ($crops as $crop): ?>
                        <option value="<?= (int) $crop['crop_id'] ?>" <?= $values['crop_id'] === (string) $crop['crop_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($crop['crop_name'] . ' — ' . $crop['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="planting_date" class="mb-1 block text-sm font-medium">Planting date</label>
                <input type="date" id="planting_date" name="planting_date" required value="<?= htmlspecialchars($values['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="expected_harvest_date" class="mb-1 block text-sm font-medium">Expected harvest date</label>
                <input type="date" id="expected_harvest_date" name="expected_harvest_date" required value="<?= htmlspecialchars($values['expected_harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="area_planted" class="mb-1 block text-sm font-medium">Area planted (hectares)</label>
                <input type="number" id="area_planted" name="area_planted" min="0.01" max="999999.99" step="0.01" required value="<?= htmlspecialchars($values['area_planted'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="seed_quantity" class="mb-1 block text-sm font-medium">Seed quantity</label>
                <input type="number" id="seed_quantity" name="seed_quantity" min="0.01" max="999999.99" step="0.01" required value="<?= htmlspecialchars($values['seed_quantity'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="status_id" class="mb-1 block text-sm font-medium">Status</label>
            <select id="status_id" name="status_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-sm">
                <option value="">Select status</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= (int) $status['status_id'] ?>" <?= $values['status_id'] === (string) $status['status_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Planting</button>
            <a href="/FMidterm_Project/plantings/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
