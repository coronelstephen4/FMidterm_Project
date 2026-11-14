<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/text_helpers.php';
require_once __DIR__ . '/../includes/validation_helpers.php';
require_once __DIR__ . '/../includes/Validator.php';
requireAdmin();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Farmer.php';

$idValue = $_GET['id'] ?? '';
$farmerId = is_string($idValue) ? (int) $idValue : 0;

if ($farmerId < 1) {
    $_SESSION['error'] = 'Farmer not found.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

$pdo = null;
try {
    $database = new Database();
    $farmerModel = new Farmer($database->connect());
    $farmer = $farmerModel->getById($farmerId);
} catch (Throwable) {
    $_SESSION['error'] = 'Farmer could not be loaded right now. Please try again later.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

if (!$farmer) {
    $_SESSION['error'] = 'Farmer not found.';
    header('Location: /FMidterm_Project/farmers/');
    exit;
}

$phoneParts = phoneFormParts((string) $farmer['contact_number']);
$values = [
    'first_name' => $farmer['first_name'],
    'last_name' => $farmer['last_name'],
    'country_iso' => $phoneParts['country_iso'],
    'contact_number' => $phoneParts['national_number'],
    'address' => $farmer['address'],
    'date_registered' => $farmer['date_registered']
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = Validator::readFields(array_keys($values), $_POST);
    $validator = new Validator();
    $validated = $validator->validateFarmer($values, $farmer);
    $errors = $validator->errors();

    if (!$errors) {
        try {
            if (!$validator->validateFarmerDuplicate($farmerModel, $values, $farmerId)) {
                $errors = $validator->errors();
            } else {
                $farmerModel->update(
                    $farmerId,
                    capitalizeInput($values['first_name']),
                    capitalizeInput($values['last_name']),
                    $validated['contact_number'],
                    capitalizeInput($values['address']),
                    $values['date_registered']
                );

                $_SESSION['success'] = 'Farmer updated successfully.';
                header('Location: /FMidterm_Project/farmers/view.php?id=' . $farmerId);
                exit;
            }
        } catch (Throwable $error) {
            $errors[] = isDuplicateDatabaseError($error)
                ? 'A farmer with the same name and address already exists. Review the existing record before adding another.'
                : 'Farmer could not be updated. Please check the details and try again.';
        }
    }
}

$pageTitle = 'Edit Farmer';
require __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Edit Farmer</h1>
    <p class="mt-1 text-sm text-slate-600">Update the farmer's details below.</p>

    <?php if ($errors): ?>
        <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <ul class="list-inside list-disc">
                <?php foreach (array_unique($errors) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/FMidterm_Project/farmers/edit.php?id=<?= $farmerId ?>" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1 block text-sm font-medium">First name <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="text" id="first_name" name="first_name" minlength="2" maxlength="50" pattern="[\p{L} .'-]+" required value="<?= htmlspecialchars($values['first_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="last_name" class="mb-1 block text-sm font-medium">Last name <span class="text-rose-600" aria-hidden="true">*</span></label>
                <input type="text" id="last_name" name="last_name" minlength="2" maxlength="50" pattern="[\p{L} .'-]+" required value="<?= htmlspecialchars($values['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label for="contact_number" class="mb-1 block text-sm font-medium">Contact number <span class="text-rose-600" aria-hidden="true">*</span></label>
            <div class="grid gap-2 sm:grid-cols-[minmax(12rem,0.8fr)_1.2fr]">
                <div class="relative">
                    <img id="country_flag" src="https://flagcdn.com/w40/<?= strtolower(htmlspecialchars($values['country_iso'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?>.png" alt="Country flag" width="24" height="18" class="pointer-events-none absolute left-3 top-1/2 z-10 h-[18px] w-6 -translate-y-1/2 rounded-sm object-cover">
                    <select id="country_iso" name="country_iso" aria-label="Country calling code" required class="w-full rounded-md border border-slate-300 py-2 pl-12 pr-3">
                    <?php foreach (countryPhoneOptions() as $iso => $country): ?>
                        <option value="<?= htmlspecialchars($iso, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-dial="<?= htmlspecialchars($country['dial_code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-trunk="<?= htmlspecialchars($country['trunk'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-name="<?= htmlspecialchars($country['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= $values['country_iso'] === $iso ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country['name'] . ' (+' . $country['dial_code'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <input type="tel" id="contact_number" name="contact_number" inputmode="tel" <?= $phoneParts['supported'] ? 'pattern="[0-9 +().-]+"' : '' ?> maxlength="24" required value="<?= htmlspecialchars($values['contact_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Mobile or phone number" class="w-full rounded-md border border-slate-300 px-3 py-2">
            </div>
            <p id="phone_preview" class="mt-1 text-sm text-slate-500" aria-live="polite"></p>
        </div>
        <div>
            <label for="address" class="mb-1 block text-sm font-medium">Address <span class="text-rose-600" aria-hidden="true">*</span></label>
            <textarea id="address" name="address" minlength="5" maxlength="150" required rows="3" class="w-full capitalize rounded-md border border-slate-300 px-3 py-2"><?= htmlspecialchars($values['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </div>
        <div>
            <label for="date_registered" class="mb-1 block text-sm font-medium">Date registered <span class="text-rose-600" aria-hidden="true">*</span></label>
            <input type="date" id="date_registered" name="date_registered" max="<?= date('Y-m-d') ?>" required value="<?= htmlspecialchars($values['date_registered'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:max-w-xs">
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="/FMidterm_Project/farmers/view.php?id=<?= $farmerId ?>" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-slate-50">Cancel</a>
        </div>
    </form>
    <script>
        (() => {
            const country = document.getElementById('country_iso');
            const phone = document.getElementById('contact_number');
            const preview = document.getElementById('phone_preview');
            const flag = document.getElementById('country_flag');
            const updatePreview = () => {
                const option = country.selectedOptions[0];
                let digits = phone.value.replace(/\D/g, '');
                const dial = option.dataset.dial || '';
                const trunk = option.dataset.trunk || '';
                flag.src = `https://flagcdn.com/w40/${country.value.toLowerCase()}.png`;
                flag.alt = `${option.dataset.name} flag`;
                if (digits.startsWith(dial) && digits.length > dial.length) digits = digits.slice(dial.length);
                if (trunk && digits.startsWith(trunk)) digits = digits.slice(trunk.length);
                const grouped = digits.length === 10
                    ? `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`
                    : digits.replace(/(.{3})/g, '$1 ').trim();
                preview.textContent = digits ? `Saved as +${dial} ${grouped}` : `Country code: +${dial}`;
            };
            country.addEventListener('change', updatePreview);
            phone.addEventListener('input', updatePreview);
            updatePreview();
        })();
    </script>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
