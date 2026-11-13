<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/text_helpers.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farmer.php';

$values = [
    'first_name' => '',
    'last_name' => '',
    'contact_number' => '',
    'address' => '',
    'date_registered' => date('Y-m-d')
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $currentValue) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $values[$field] = in_array($field, ['first_name', 'last_name', 'address'], true)
                ? capitalizeInput($_POST[$field])
                : trim($_POST[$field]);
        }
    }

    foreach (['first_name', 'last_name', 'contact_number', 'address', 'date_registered'] as $field) {
        if ($values[$field] === '') {
            $errors[] = 'All fields are required.';
            break;
        }
    }

    if (mb_strlen($values['first_name']) > 50 || mb_strlen($values['last_name']) > 50) {
        $errors[] = 'Names must be 50 characters or fewer.';
    }
    if (mb_strlen($values['contact_number']) > 20) {
        $errors[] = 'Contact number must be 20 characters or fewer.';
    }
    if (mb_strlen($values['address']) > 150) {
        $errors[] = 'Address must be 150 characters or fewer.';
    }

    $dateParts = explode('-', $values['date_registered']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['date_registered'])
        || count($dateParts) !== 3
        || !checkdate((int) ($dateParts[1] ?? 0), (int) ($dateParts[2] ?? 0), (int) ($dateParts[0] ?? 0))) {
        $errors[] = 'Enter a valid registration date.';
    } elseif ($values['date_registered'] > date('Y-m-d')) {
        $errors[] = 'Registration date cannot be in the future.';
    }

    if (!$errors) {
        try {
            $database = new Database();
            $farmerModel = new Farmer($database->connect());
            if ($farmerModel->hasDuplicate(
                $values['first_name'],
                $values['last_name'],
                $values['contact_number'],
                $values['address']
            )) {
                $errors[] = 'A farmer with a matching contact and address, or the same name and address, already exists. Review the existing record before adding another.';
            } else {
                $farmerModel->create(
                    $values['first_name'],
                    $values['last_name'],
                    $values['contact_number'],
                    $values['address'],
                    $values['date_registered']
                );

                $_SESSION['success'] = 'Farmer created successfully.';
                header('Location: /FMidterm_Project/farmers/');
                exit;
            }
        } catch (Throwable $error) {
            $errors[] = isDuplicateDatabaseError($error)
                ? 'A farmer with a matching contact and address, or the same name and address, already exists. Review the existing record before adding another.'
                : 'Farmer could not be saved. Please check the details and try again.';
        }
    }
}

$pageTitle = 'Add Farmer';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Add Farmer</h1>
    <p class="mt-1 text-sm text-slate-600">Enter the farmer's details below.</p>

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
                <label for="first_name" class="mb-1 block text-sm font-medium">First name</label>
                <input type="text" id="first_name" name="first_name" maxlength="50" required value="<?= htmlspecialchars($values['first_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="last_name" class="mb-1 block text-sm font-medium">Last name</label>
                <input type="text" id="last_name" name="last_name" maxlength="50" required value="<?= htmlspecialchars($values['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="contact_number" class="mb-1 block text-sm font-medium">Contact number</label>
            <input type="text" id="contact_number" name="contact_number" maxlength="20" required value="<?= htmlspecialchars($values['contact_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label for="address" class="mb-1 block text-sm font-medium">Address</label>
            <textarea id="address" name="address" maxlength="150" required rows="3" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2"><?= htmlspecialchars($values['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </div>
        <div>
            <label for="date_registered" class="mb-1 block text-sm font-medium">Date registered</label>
            <input type="date" id="date_registered" name="date_registered" max="<?= date('Y-m-d') ?>" required value="<?= htmlspecialchars($values['date_registered'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Farmer</button>
            <a href="/FMidterm_Project/farmers/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
