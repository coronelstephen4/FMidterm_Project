<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/text_helpers.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$idValue = $_GET['id'] ?? '';
$farmId = is_string($idValue) ? (int) $idValue : 0;

if ($farmId < 1) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: ' . APP_BASE_PATH . '/farms/');
    exit;
}

$pageError = null;
$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $farmModel = new Farm($pdo);
    $farm = $farmModel->getById($farmId);
    $farmers = $farmModel->getFarmers();
    $soilTypes = $farmModel->getSoilTypes();
    $irrigationTypes = $farmModel->getIrrigationTypes();
} catch (Throwable) {
    $_SESSION['error'] = 'Farm could not be loaded right now. Please try again later.';
    header('Location: ' . APP_BASE_PATH . '/farms/');
    exit;
}

if (!$farm) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: ' . APP_BASE_PATH . '/farms/');
    exit;
}

$values = [
    'farmer_id' => (string) $farm['farmer_id'],
    'farm_name' => $farm['farm_name'],
    'location' => $farm['location'],
    'area_hectares' => (string) $farm['area_hectares'],
    'soil_type_id' => (string) $farm['soil_type_id'],
    'irrigation_type_id' => (string) $farm['irrigation_type_id']
];
$errors = [];
$duplicateNotice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    $validated = $validator->validateFarm($pdo, $values);
    $errors = $validator->errors();
    $farmerId = $validated['farmer_id'] ?? 0;
    $soilTypeId = $validated['soil_type_id'] ?? 0;
    $irrigationTypeId = $validated['irrigation_type_id'] ?? 0;

    if (!$errors) {
        try {
            if (!$farmModel->hasValidReferences($farmerId, $irrigationTypeId, $soilTypeId)) {
                $errors[] = 'Choose a valid farmer, soil type, and irrigation type.';
            }
        } catch (Throwable) {
            $errors[] = 'Farm details could not be checked right now. Please try again later.';
        }
    }

    $duplicateChecker = null;
    if (!$errors) {
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findFarm(
                $farmerId,
                $values['farm_name'],
                $values['location'],
                $validated['area_hectares'],
                $irrigationTypeId,
                $soilTypeId,
                $farmId
            );
            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Farm', $match);
            }
        } catch (Throwable) {
            $errors[] = 'Farm details could not be checked for duplicates. Please try again.';
        }
    }

    if (!$errors && !$duplicateNotice) {
        try {
            $updated = $farmModel->update(
                $farmId,
                $farmerId,
                $values['farm_name'],
                $values['location'],
                $validated['area_hectares'],
                $soilTypeId,
                $irrigationTypeId
            );

            if (!$updated) {
                throw new RuntimeException('Farm was not updated.');
            }

            $_SESSION['success'] = 'Farm updated successfully.';
            header('Location: ' . APP_BASE_PATH . '/farms/view.php?id=' . $farmId);
            exit;
        } catch (Throwable $error) {
            if (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                try {
                    $match = $duplicateChecker->findFarm(
                        $farmerId,
                        $values['farm_name'],
                        $values['location'],
                        $validated['area_hectares'],
                        $irrigationTypeId,
                        $soilTypeId,
                        $farmId
                    );
                } catch (Throwable) {
                }
                $duplicateNotice = DuplicateChecker::notice('Farm', $match);
            } else {
                $errors[] = 'Farm could not be updated. Please check the details and try again.';
            }
        }
    }
}

$pageTitle = 'Edit Farm';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Edit Farm</h1>
    <p class="mt-1 text-sm text-slate-600">Update the farm's details below.</p>

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

    <form method="post" action="<?= APP_BASE_PATH ?>/farms/edit.php?id=<?= $farmId ?>" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div>
            <label for="farmer_id" class="mb-1 block text-sm font-medium">Farmer <span class="text-rose-600" aria-hidden="true">*</span></label>
            <select id="farmer_id" name="farmer_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                <option value="">Select a farmer</option>
                <?php foreach ($farmers as $farmerOption): ?>
                    <option value="<?= (int) $farmerOption['farmer_id'] ?>" <?= $values['farmer_id'] === (string) $farmerOption['farmer_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($farmerOption['first_name'] . ' ' . $farmerOption['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="farm_name" class="mb-1 block text-sm font-medium">Farm name <span class="text-rose-600" aria-hidden="true">*</span></label>
            <input type="text" id="farm_name" name="farm_name" minlength="2" maxlength="100" required value="<?= htmlspecialchars($values['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label for="location" class="mb-1 block text-sm font-medium">Location <span class="text-rose-600" aria-hidden="true">*</span></label>
            <input type="text" id="location" name="location" minlength="2" maxlength="150" required value="<?= htmlspecialchars($values['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label for="area_hectares" class="mb-1 block text-sm font-medium">Area (hectares) <span class="text-rose-600" aria-hidden="true">*</span></label>
            <input type="number" id="area_hectares" name="area_hectares" min="0.01" max="10000" step="0.01" required value="<?= htmlspecialchars($values['area_hectares'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="soil_type_id" class="mb-1 block text-sm font-medium">Soil type <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="soil_type_id" name="soil_type_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select soil type</option>
                    <?php foreach ($soilTypes as $soilType): ?>
                        <option value="<?= (int) $soilType['soil_type_id'] ?>" <?= $values['soil_type_id'] === (string) $soilType['soil_type_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($soilType['soil_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="irrigation_type_id" class="mb-1 block text-sm font-medium">Irrigation type <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="irrigation_type_id" name="irrigation_type_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select irrigation type</option>
                    <?php foreach ($irrigationTypes as $type): ?>
                        <option value="<?= (int) $type['irrigation_type_id'] ?>" <?= $values['irrigation_type_id'] === (string) $type['irrigation_type_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['irrigation_type_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="<?= APP_BASE_PATH ?>/farms/view.php?id=<?= $farmId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
