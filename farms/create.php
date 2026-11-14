<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/text_helpers.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$values = [
    'farmer_id' => '',
    'farm_name' => '',
    'location' => '',
    'area_hectares' => '',
    'soil_type_id' => '',
    'irrigation_type_id' => ''
];
$farmers = [];
$soilTypes = [];
$irrigationTypes = [];
$errors = [];
$duplicateNotice = null;
$pageError = null;

$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $farmModel = new Farm($pdo);
    $farmers = $farmModel->getFarmers();
    $soilTypes = $farmModel->getSoilTypes();
    $irrigationTypes = $farmModel->getIrrigationTypes();
} catch (Throwable) {
    $pageError = 'Farm form data could not be loaded right now. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    if ($pdo instanceof PDO) {
        $validated = $validator->validateFarm($pdo, $values);
    } else {
        $validated = $values;
        $errors[] = $pageError ?? "Farm options could not be checked right now. Please try again later.";
    }
    $errors = $validator->errors();
    $farmerId = $validated['farmer_id'] ?? 0;
    $soilTypeId = $validated['soil_type_id'] ?? 0;
    $irrigationTypeId = $validated['irrigation_type_id'] ?? 0;

    if (!$errors && !$pageError) {
        try {
            if (!$farmModel->hasValidReferences($farmerId, $irrigationTypeId, $soilTypeId)) {
                $errors[] = 'Choose a valid farmer, soil type, and irrigation type.';
            }
        } catch (Throwable) {
            $errors[] = 'Farm details could not be checked right now. Please try again later.';
        }
    }

    $duplicateChecker = null;
    if (!$errors && !$pageError) {
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findFarm(
                $farmerId,
                $values['farm_name'],
                $values['location'],
                $validated['area_hectares'],
                $irrigationTypeId,
                $soilTypeId
            );
            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Farm', $match);
            }
        } catch (Throwable) {
            $errors[] = 'Farm details could not be checked for duplicates. Please try again.';
        }
    }

    if (!$errors && !$pageError && !$duplicateNotice) {
        try {
            $farmId = $farmModel->create(
                $farmerId,
                $values['farm_name'],
                $values['location'],
                $validated['area_hectares'],
                $soilTypeId,
                $irrigationTypeId
            );

            if ($farmId < 1) {
                throw new RuntimeException('Farm was not created.');
            }

            $_SESSION['success'] = 'Farm created successfully.';
            header('Location: /FMidterm_Project/farms/view.php?id=' . $farmId);
            exit;
        } catch (Throwable $error) {
            if (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                if ($duplicateChecker) {
                    try {
                        $match = $duplicateChecker->findFarm(
                            $farmerId,
                            $values['farm_name'],
                            $values['location'],
                            $validated['area_hectares'],
                            $irrigationTypeId,
                            $soilTypeId
                        );
                    } catch (Throwable) {
                    }
                }
                $duplicateNotice = DuplicateChecker::notice('Farm', $match);
            } else {
                $errors[] = 'Farm could not be saved. Please check the details and try again.';
            }
        }
    }
}

$pageTitle = 'Add Farm';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Add Farm</h1>
    <p class="mt-1 text-sm text-slate-600">Select a farmer and enter the farm details.</p>

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
            <label for="farmer_id" class="mb-1 block text-sm font-medium">Farmer <span class="text-rose-600" aria-hidden="true">*</span></label>
            <select id="farmer_id" name="farmer_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                <option value="">Select a farmer</option>
                <?php foreach ($farmers as $farmer): ?>
                    <option value="<?= (int) $farmer['farmer_id'] ?>" <?= $values['farmer_id'] === (string) $farmer['farmer_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
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
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Farm</button>
            <a href="/FMidterm_Project/farms/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
