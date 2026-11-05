<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Crop.php';

$idValue = $_GET['id'] ?? '';
$cropId = is_string($idValue) ? (int) $idValue : 0;

if ($cropId < 1) {
    $_SESSION['error'] = 'Crop not found.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

try {
    $database = new Database();
    $cropModel = new Crop($database->connect());
    $crop = $cropModel->getById($cropId);
    $categories = $cropModel->getCategories();
    $seasons = $cropModel->getRecommendedSeasons();
} catch (Throwable) {
    $_SESSION['error'] = 'Crop could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

if (!$crop) {
    $_SESSION['error'] = 'Crop not found.';
    header('Location: /FMidterm_Project/crops/');
    exit;
}

$values = [
    'crop_name' => $crop['crop_name'],
    'variety' => $crop['variety'],
    'growing_period_days' => (string) $crop['growing_period_days'],
    'category_id' => (string) $crop['crop_category_id'],
    'season_id' => (string) $crop['recommended_season_id']
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $currentValue) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $values[$field] = trim($_POST[$field]);
        }
    }

    if ($values['crop_name'] === '' || $values['variety'] === '' || $values['growing_period_days'] === ''
        || $values['category_id'] === '' || $values['season_id'] === '') {
        $errors[] = 'All fields are required.';
    }
    if (mb_strlen($values['crop_name']) > 80 || mb_strlen($values['variety']) > 80) {
        $errors[] = 'Crop name and variety must be 80 characters or fewer.';
    }

    $growingPeriodDigits = ltrim($values['growing_period_days'], '0');
    $growingPeriodDigits = $growingPeriodDigits === '' ? '0' : $growingPeriodDigits;
    $growingPeriodFits = ctype_digit($values['growing_period_days'])
        && (strlen($growingPeriodDigits) < 10
            || (strlen($growingPeriodDigits) === 10 && strcmp($growingPeriodDigits, '2147483647') <= 0));
    $growingPeriod = $growingPeriodFits ? (int) $growingPeriodDigits : 0;
    $categoryId = ctype_digit($values['category_id']) ? (int) $values['category_id'] : 0;
    $seasonId = ctype_digit($values['season_id']) ? (int) $values['season_id'] : 0;

    if (!$growingPeriodFits || $growingPeriod < 1) {
        $errors[] = 'Growing period must be a positive whole number of days.';
    }
    if ($categoryId < 1 || $seasonId < 1) {
        $errors[] = 'Select a valid crop category and recommended season.';
    }

    if (!$errors) {
        try {
            if (!$cropModel->hasValidReferences($categoryId, $seasonId)) {
                $errors[] = 'Select a valid crop category and recommended season.';
            }
        } catch (Throwable) {
            $errors[] = 'Crop options could not be checked right now. Please try again later.';
        }
    }

    if (!$errors) {
        try {
            $updated = $cropModel->update(
                $cropId,
                $values['crop_name'],
                $values['variety'],
                $growingPeriod,
                $categoryId,
                $seasonId
            );

            if (!$updated) {
                throw new RuntimeException('Crop was not updated.');
            }

            $_SESSION['success'] = 'Crop updated successfully.';
            header('Location: /FMidterm_Project/crops/view.php?id=' . $cropId);
            exit;
        } catch (Throwable) {
            $errors[] = 'Crop could not be updated. Please check the details and try again.';
        }
    }
}

$pageTitle = 'Edit Crop';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Edit Crop</h1>
    <p class="mt-1 text-sm text-slate-600">Update crop and growing information.</p>

    <?php if ($errors): ?>
        <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <ul class="list-inside list-disc">
                <?php foreach (array_unique($errors) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/FMidterm_Project/crops/edit.php?id=<?= $cropId ?>" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="crop_name" class="mb-1 block text-sm font-medium">Crop name</label>
                <input type="text" id="crop_name" name="crop_name" maxlength="80" required value="<?= htmlspecialchars($values['crop_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="variety" class="mb-1 block text-sm font-medium">Variety</label>
                <input type="text" id="variety" name="variety" maxlength="80" required value="<?= htmlspecialchars($values['variety'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="growing_period_days" class="mb-1 block text-sm font-medium">Growing period (days)</label>
            <input type="number" id="growing_period_days" name="growing_period_days" min="1" step="1" required value="<?= htmlspecialchars($values['growing_period_days'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="category_id" class="mb-1 block text-sm font-medium">Crop category</label>
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
                <label for="season_id" class="mb-1 block text-sm font-medium">Recommended season</label>
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
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="/FMidterm_Project/crops/view.php?id=<?= $cropId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
