<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$today = date('Y-m-d');
$values = [
    'farm_id' => '',
    'variety_id' => '',
    'planting_date' => $today,
    'area_planted' => '',
    'seed_quantity' => '',
    'expected_harvest_date' => '',
    'status_id' => '2'
];
$farms = [];
$crops = [];
$statuses = [];
$errors = [];
$duplicateNotice = null;
$pageError = null;

$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $plantingModel = new Planting($pdo);
    $farms = $plantingModel->getFarms();
    $crops = $plantingModel->getCrops();
    $statuses = $plantingModel->getStatuses();
    foreach ($statuses as $status) {
        if ($status['status_name'] === 'Growing') {
            $values['status_id'] = (string) $status['status_id'];
            break;
        }
    }
} catch (Throwable) {
    $pageError = 'Planting form data could not be loaded right now. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    $validated = $values;
    if ($pdo instanceof PDO) {
        try {
            $growingPeriodDays = null;
            if ($values['expected_harvest_date'] === '' && ctype_digit($values['variety_id'])) {
                $growingPeriodDays = $plantingModel->getGrowingPeriodDays((int) $values['variety_id']);
            }
            $validated = $validator->validatePlanting($pdo, $values, null, $growingPeriodDays);
        } catch (Throwable) {
            $errors[] = 'Planting options could not be checked right now. Please try again later.';
        }
    } else {
        $errors[] = $pageError ?? "Planting options could not be checked right now. Please try again later.";
    }
    $errors = array_merge($errors, $validator->errors());
    $values['expected_harvest_date'] = $validated['expected_harvest_date'] ?? $values['expected_harvest_date'];
    $farmId = $validated['farm_id'] ?? 0;
    $varietyId = $validated['variety_id'] ?? 0;
    $statusId = $validated['status_id'] ?? 0;

    if (!$errors && !$pageError) {
        try {
            if (!$plantingModel->hasValidReferences($farmId, $varietyId, $statusId)) {
                $errors[] = 'Select a valid farm, crop, and planting status.';
            }
        } catch (Throwable) {
            $errors[] = 'Planting options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        $duplicateChecker = null;
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findPlanting(
                $farmId,
                $varietyId,
                $values['planting_date']
            );

            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Planting', $match);
            } else {
                $plantingId = $plantingModel->create(
                    $farmId,
                    $varietyId,
                    $values['planting_date'],
                    $validated['area_planted'],
                    $validated['seed_quantity'],
                    $values['expected_harvest_date'],
                    $statusId
                );

                if ($plantingId < 1) {
                    throw new RuntimeException('Planting was not created.');
                }

                $_SESSION['success'] = 'Planting record created successfully.';
                header('Location: ' . APP_BASE_PATH . '/plantings/view.php?id=' . $plantingId);
                exit;
            }
        } catch (Throwable $error) {
            if (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                try {
                    if ($duplicateChecker instanceof DuplicateChecker) {
                        $match = $duplicateChecker->findPlanting($farmId, $varietyId, $values['planting_date']);
                    }
                } catch (Throwable) {
                }
                $duplicateNotice = DuplicateChecker::notice('Planting', $match);
            } else {
                $errors[] = 'Planting could not be saved. Please check the details and try again.';
            }
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
    <?php require __DIR__ . '/../includes/duplicate_notice.php'; ?>

    <form method="post" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="farm_id" class="mb-1 block text-sm font-medium">Farm <span class="text-rose-600" aria-hidden="true">*</span></label>
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
                <label for="variety_id" class="mb-1 block text-sm font-medium">Crop variety <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="variety_id" name="variety_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select crop variety</option>
                    <?php foreach ($crops as $crop): ?>
                        <option value="<?= (int) $crop['variety_id'] ?>" data-growing-period="<?= (int) $crop['growing_period_days'] ?>" <?= $values['variety_id'] === (string) $crop['variety_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($crop['crop_name'] . ' — ' . $crop['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="planting_date" class="mb-1 block text-sm font-medium">Planting date <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="date" id="planting_date" name="planting_date" required value="<?= htmlspecialchars($values['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="expected_harvest_date" class="mb-1 block text-sm font-medium">Expected harvest date <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="date" id="expected_harvest_date" name="expected_harvest_date" min="<?= htmlspecialchars($values['planting_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required value="<?= htmlspecialchars($values['expected_harvest_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="area_planted" class="mb-1 block text-sm font-medium">Area planted (hectares) <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="number" id="area_planted" name="area_planted" min="0.01" max="10000" step="0.01" required value="<?= htmlspecialchars($values['area_planted'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="seed_quantity" class="mb-1 block text-sm font-medium">Seed quantity <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="number" id="seed_quantity" name="seed_quantity" min="0.01" max="999999.99" step="0.01" required value="<?= htmlspecialchars($values['seed_quantity'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="status_id" class="mb-1 block text-sm font-medium">Status <span class="text-rose-600" aria-hidden="true">*</span></label>
            <select id="status_id" name="status_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-sm">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= (int) $status['status_id'] ?>" <?= $values['status_id'] === (string) $status['status_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status['status_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Planting</button>
            <a href="<?= APP_BASE_PATH ?>/plantings/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<script>
    (() => {
        const variety = document.getElementById('variety_id');
        const plantingDate = document.getElementById('planting_date');
        const expectedDate = document.getElementById('expected_harvest_date');
        const updateExpectedDate = () => {
            const days = Number(variety.selectedOptions[0]?.dataset.growingPeriod || 0);
            if (!plantingDate.value || !days) return;
            const date = new Date(`${plantingDate.value}T00:00:00Z`);
            date.setUTCDate(date.getUTCDate() + days);
            expectedDate.value = date.toISOString().slice(0, 10);
        };
        variety.addEventListener('change', updateExpectedDate);
        plantingDate.addEventListener('change', updateExpectedDate);
        if (!expectedDate.value) updateExpectedDate();
    })();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
