<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farm.php';

$idValue = $_GET['id'] ?? '';
$farmId = is_string($idValue) ? (int) $idValue : 0;

if ($farmId < 1) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: /FMidterm_Project/farms/');
    exit;
}

$pageError = null;
try {
    $database = new Database();
    $farmModel = new Farm($database->connect());
    $farm = $farmModel->getById($farmId);
    $farmers = $farmModel->getFarmers();
    $soilTypes = $farmModel->getSoilTypes();
    $irrigationTypes = $farmModel->getIrrigationTypes();
} catch (Throwable) {
    $_SESSION['error'] = 'Farm could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/farms/');
    exit;
}

if (!$farm) {
    $_SESSION['error'] = 'Farm not found.';
    header('Location: /FMidterm_Project/farms/');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $currentValue) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $values[$field] = trim($_POST[$field]);
        }
    }

    if ($values['farmer_id'] === '' || $values['farm_name'] === '' || $values['location'] === ''
        || $values['area_hectares'] === '' || $values['soil_type_id'] === '' || $values['irrigation_type_id'] === '') {
        $errors[] = 'All fields are required.';
    }
    if (mb_strlen($values['farm_name']) > 100) {
        $errors[] = 'Farm name must be 100 characters or fewer.';
    }
    if (mb_strlen($values['location']) > 150) {
        $errors[] = 'Location must be 150 characters or fewer.';
    }

    $areaPattern = '/^\d{1,6}(\.\d{1,2})?$/';
    if (!preg_match($areaPattern, $values['area_hectares']) || (float) $values['area_hectares'] <= 0) {
        $errors[] = 'Area must be a positive number with up to two decimal places.';
    }

    $farmerId = ctype_digit($values['farmer_id']) ? (int) $values['farmer_id'] : 0;
    $soilTypeId = ctype_digit($values['soil_type_id']) ? (int) $values['soil_type_id'] : 0;
    $irrigationTypeId = ctype_digit($values['irrigation_type_id']) ? (int) $values['irrigation_type_id'] : 0;

    if ($farmerId < 1 || $soilTypeId < 1 || $irrigationTypeId < 1) {
        $errors[] = 'Choose a valid farmer, soil type, and irrigation type.';
    }

    if (!$errors) {
        try {
            if (!$farmModel->hasValidReferences($farmerId, $irrigationTypeId, $soilTypeId)) {
                $errors[] = 'Choose a valid farmer, soil type, and irrigation type.';
            }
        } catch (Throwable) {
            $errors[] = 'Farm details could not be checked right now. Please try again later.';
        }
    }

    if (!$errors) {
        try {
            $updated = $farmModel->update(
                $farmId,
                $farmerId,
                $values['farm_name'],
                $values['location'],
                (float) $values['area_hectares'],
                $soilTypeId,
                $irrigationTypeId
            );

            if (!$updated) {
                throw new RuntimeException('Farm was not updated.');
            }

            $_SESSION['success'] = 'Farm updated successfully.';
            header('Location: /FMidterm_Project/farms/view.php?id=' . $farmId);
            exit;
        } catch (Throwable) {
            $errors[] = 'Farm could not be updated. Please check the details and try again.';
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

    <form method="post" action="/FMidterm_Project/farms/edit.php?id=<?= $farmId ?>" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div>
            <label for="farmer_id" class="mb-1 block text-sm font-medium">Farmer</label>
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
            <label for="farm_name" class="mb-1 block text-sm font-medium">Farm name</label>
            <input type="text" id="farm_name" name="farm_name" maxlength="100" required value="<?= htmlspecialchars($values['farm_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label for="location" class="mb-1 block text-sm font-medium">Location</label>
            <input type="text" id="location" name="location" maxlength="150" required value="<?= htmlspecialchars($values['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label for="area_hectares" class="mb-1 block text-sm font-medium">Area (hectares)</label>
            <input type="number" id="area_hectares" name="area_hectares" min="0.01" max="999999.99" step="0.01" required value="<?= htmlspecialchars($values['area_hectares'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="soil_type_id" class="mb-1 block text-sm font-medium">Soil type</label>
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
                <label for="irrigation_type_id" class="mb-1 block text-sm font-medium">Irrigation type</label>
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
            <a href="/FMidterm_Project/farms/view.php?id=<?= $farmId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
