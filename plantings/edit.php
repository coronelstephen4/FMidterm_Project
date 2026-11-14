<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Planting.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$idValue = $_GET['id'] ?? '';
$plantingId = is_string($idValue) ? (int) $idValue : 0;

if ($plantingId < 1) {
    $_SESSION['error'] = 'Planting record not found.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $plantingModel = new Planting($pdo);
    $planting = $plantingModel->getById($plantingId);
    $farms = $plantingModel->getFarms();
    $crops = $plantingModel->getCrops();
    $statuses = $plantingModel->getStatuses();
} catch (Throwable) {
    $_SESSION['error'] = 'Planting record could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

if (!$planting) {
    $_SESSION['error'] = 'Planting record not found.';
    header('Location: /FMidterm_Project/plantings/');
    exit;
}

$values = [
    'farm_id' => (string) $planting['farm_id'],
    'variety_id' => (string) $planting['variety_id'],
    'planting_date' => $planting['planting_date'],
    'area_planted' => (string) $planting['area_planted'],
    'seed_quantity' => (string) $planting['seed_quantity'],
    'expected_harvest_date' => $planting['expected_harvest_date'],
    'status_id' => (string) $planting['status_id']
];
$errors = [];
$duplicateNotice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    $validated = $values;
    try {
        $growingPeriodDays = null;
        if ($values['expected_harvest_date'] === '' && ctype_digit($values['variety_id'])) {
            $growingPeriodDays = $plantingModel->getGrowingPeriodDays((int) $values['variety_id']);
        }
        $validated = $validator->validatePlanting($pdo, $values, $planting, $growingPeriodDays);
    } catch (Throwable) {
        $errors[] = 'Planting options could not be checked right now. Please try again later.';
    }
    $errors = array_merge($errors, $validator->errors());
    $values['expected_harvest_date'] = $validated['expected_harvest_date'] ?? $values['expected_harvest_date'];
    $farmId = $validated['farm_id'] ?? 0;
    $varietyId = $validated['variety_id'] ?? 0;
    $statusId = $validated['status_id'] ?? 0;

    if (!$errors) {
        $duplicateChecker = null;
        try {
            if (!$plantingModel->hasValidReferences($farmId, $varietyId, $statusId)) {
                $errors[] = 'Select a valid farm, crop, and planting status.';
            }
        } catch (Throwable) {
            $errors[] = 'Planting options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors) {
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findPlanting(
                $farmId,
                $varietyId,
                $values['planting_date'],
                $plantingId
            );

            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Planting', $match);
            } else {
                $updated = $plantingModel->update(
                    $plantingId,
                    $farmId,
                    $varietyId,
                    $values['planting_date'],
                    $validated['area_planted'],
                    $validated['seed_quantity'],
                    $values['expected_harvest_date'],
                    $statusId
                );

                if (!$updated) {
                    throw new RuntimeException('Planting record was not updated.');
                }

                $_SESSION['success'] = 'Planting record updated successfully.';
                header('Location: /FMidterm_Project/plantings/view.php?id=' . $plantingId);
                exit;
            }
        } catch (Throwable $error) {
            if (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                try {
                    if ($duplicateChecker instanceof DuplicateChecker) {
                        $match = $duplicateChecker->findPlanting(
                            $farmId,
                            $varietyId,
                            $values['planting_date'],
                            $plantingId
                        );
                    }
                } catch (Throwable) {
                }
                $duplicateNotice = DuplicateChecker::notice('Planting', $match);
            } else {
                $errors[] = 'Planting record could not be updated. Please check the details and try again.';
            }
        }
    }
}

$pageTitle = 'Edit Planting';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Edit Planting</h1>
    <p class="mt-1 text-sm text-slate-600">Update the planting operation details.</p>

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

    <form method="post" action="/FMidterm_Project/plantings/edit.php?id=<?= $plantingId ?>" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
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
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="/FMidterm_Project/plantings/view.php?id=<?= $plantingId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
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
    })();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
