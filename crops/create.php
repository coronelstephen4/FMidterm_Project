<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/text_helpers.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Crop.php';
require_once __DIR__ . '/../includes/DuplicateChecker.php';

$values = [
    'crop_name' => '',
    'variety' => '',
    'growing_period_days' => '',
    'category_id' => '',
    'season_id' => ''
];
$categories = [];
$seasons = [];
$errors = [];
$duplicateNotice = null;
$pageError = null;

$pdo = null;
try {
    $database = new Database();
    $pdo = $database->connect();
    $cropModel = new Crop($pdo);
    $categories = $cropModel->getCategories();
    $seasons = $cropModel->getRecommendedSeasons();
} catch (Throwable) {
    $pageError = 'Crop form data could not be loaded right now. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    if ($pdo instanceof PDO) {
        $validated = $validator->validateCrop($pdo, $values);
    } else {
        $validated = $values;
        $errors[] = $pageError ?? "Crop options could not be checked right now. Please try again later.";
    }
    $errors = $validator->errors();
    $growingPeriod = $validated['growing_period_days'] ?? 0;
    $categoryId = $validated['category_id'] ?? 0;
    $seasonId = $validated['season_id'] ?? 0;

    if (!$errors && !$pageError) {
        try {
            if (!$cropModel->hasValidReferences($categoryId, $seasonId)) {
                $errors[] = 'Select a valid crop category and recommended season.';
            }
        } catch (Throwable) {
            $errors[] = 'Crop options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors && !$pageError) {
        $duplicateChecker = null;
        $cropName = capitalizeInput($values['crop_name']);
        $varietyName = capitalizeInput($values['variety']);
        try {
            $duplicateChecker = new DuplicateChecker($pdo);
            $match = $duplicateChecker->findCropVariety($cropName, $varietyName);

            if ($match) {
                $duplicateNotice = DuplicateChecker::notice('Crop variety', $match);
            } else {
                $cropId = $cropModel->create(
                    $cropName,
                    $varietyName,
                    $growingPeriod,
                    $categoryId,
                    $seasonId
                );

                if ($cropId < 1) {
                    throw new RuntimeException('Crop was not created.');
                }

                $_SESSION['success'] = 'Crop variety created successfully.';
                header('Location: /FMidterm_Project/crops/view.php?id=' . $cropId);
                exit;
            }
        } catch (Throwable $error) {
            if ($error instanceof DomainException) {
                $errors[] = $error->getMessage();
            } elseif (DuplicateChecker::isUniqueViolation($error)) {
                $match = null;
                try {
                    if ($duplicateChecker instanceof DuplicateChecker) {
                        $match = $duplicateChecker->findCropVariety($cropName, $varietyName);
                    }
                } catch (Throwable) {
                }
                if ($match) {
                    $duplicateNotice = DuplicateChecker::notice('Crop variety', $match);
                } else {
                    $errors[] = duplicateDatabaseMessage($error)
                        ?? 'Crop could not be saved. Please check the details and try again.';
                }
            } else {
                $errors[] = duplicateDatabaseMessage($error)
                    ?? 'Crop could not be saved. Please check the details and try again.';
            }
        }
    }
}

$pageTitle = 'Add Crop Variety';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Add Crop Variety</h1>
    <p class="mt-1 text-sm text-slate-600">Add a variety. A matching crop name reuses its existing crop record and category.</p>

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
                <label for="crop_name" class="mb-1 block text-sm font-medium">Crop name <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="text" id="crop_name" name="crop_name" minlength="2" maxlength="80" pattern="[\p{L} -]+" required value="<?= htmlspecialchars($values['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="variety" class="mb-1 block text-sm font-medium">Variety <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="text" id="variety" name="variety" minlength="1" maxlength="80" required value="<?= htmlspecialchars($values['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="growing_period_days" class="mb-1 block text-sm font-medium">Growing period (days) <span class="text-rose-600" aria-hidden="true">*</span></label>
            <input type="number" id="growing_period_days" name="growing_period_days" min="1" max="730" step="1" required value="<?= htmlspecialchars($values['growing_period_days'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="category_id" class="mb-1 block text-sm font-medium">Crop category <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="category_id" name="category_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['crop_category_id'] ?>" <?= $values['category_id'] === (string) $category['crop_category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['crop_category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="season_id" class="mb-1 block text-sm font-medium">Recommended season <span class="text-rose-600" aria-hidden="true">*</span></label>
                <select id="season_id" name="season_id" required class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Select season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?= (int) $season['recommended_season_id'] ?>" <?= $values['season_id'] === (string) $season['recommended_season_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($season['recommended_season_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Variety</button>
            <a href="/FMidterm_Project/crops/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
