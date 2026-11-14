<?php
require_once __DIR__ . '/country_phone.php';

class Validator {
    private array $errors = [];

    private const REFERENCES = [
        'farmer' => ['farmer', 'farmer_id'],
        'farm' => ['farm', 'farm_id'],
        'crop' => ['crop', 'crop_id'],
        'crop_category' => ['crop_categories', 'crop_category_id'],
        'variety' => ['crop_variety', 'variety_id'],
        'season' => ['recommended_seasons', 'recommended_season_id'],
        'soil_type' => ['soil_types', 'soil_type_id'],
        'irrigation_type' => ['irrigation_types', 'irrigation_type_id'],
        'planting' => ['planting', 'planting_id'],
        'planting_status' => ['planting_statuses', 'status_id'],
        'quality_grade' => ['harvest_quality_grades', 'quality_grade_id'],
        'harvest_remark' => ['harvest_remarks', 'remark_id']
    ];

    public static function readFields(array $fields, array $source): array {
        $values = [];
        foreach ($fields as $field) {
            $value = $source[$field] ?? '';
            $values[$field] = is_string($value) ? trim($value) : '';
        }
        return $values;
    }

    public function required(string $value, string $label, bool $trim = true): bool {
        $checkedValue = $trim ? trim($value) : $value;
        if ($checkedValue === '') {
            $this->errors[] = $label . ' is required.';
            return false;
        }
        return true;
    }

    public function text(
        string $value,
        string $label,
        int $minimum,
        int $maximum,
        ?string $pattern = null,
        ?string $formatMessage = null
    ): bool {
        if (!$this->required($value, $label)) {
            return false;
        }

        preg_match_all('/./us', $value, $characters);
        $length = count($characters[0] ?? []);
        if ($length < $minimum || $length > $maximum) {
            $this->errors[] = $label . ' must be ' . $minimum . '-' . $maximum . ' characters.';
            return false;
        }

        if ($pattern !== null && !preg_match($pattern, $value)) {
            $this->errors[] = $formatMessage ?? ($label . ' has an invalid format.');
            return false;
        }

        return true;
    }

    public function integer(string $value, string $label, int $minimum, int $maximum): ?int {
        if (!$this->required($value, $label)) {
            return null;
        }

        if (!preg_match('/^\d+$/', $value)) {
            $this->errors[] = $label . ' must be a whole number.';
            return null;
        }

        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximumText = (string) $maximum;
        if (strlen($normalized) > strlen($maximumText)
            || (strlen($normalized) === strlen($maximumText) && strcmp($normalized, $maximumText) > 0)) {
            $this->errors[] = $label . ' must be between ' . $minimum . ' and ' . $maximum . '.';
            return null;
        }

        $number = (int) $normalized;
        if ($number < $minimum) {
            $this->errors[] = $label . ' must be between ' . $minimum . ' and ' . $maximum . '.';
            return null;
        }

        return $number;
    }

    public function decimal(
        string $value,
        string $label,
        float $minimum,
        float $maximum,
        int $decimalPlaces = 2
    ): ?float {
        if (!$this->required($value, $label)) {
            return null;
        }

        $pattern = '/^-?\d+(?:\.\d{1,' . $decimalPlaces . '})?$/';
        if (!preg_match($pattern, $value)) {
            $this->errors[] = $label . ' must be a number with up to ' . $decimalPlaces . ' decimal places.';
            return null;
        }

        $number = (float) $value;
        if (!is_finite($number) || $number < $minimum || $number > $maximum) {
            $this->errors[] = $label . ' must be between ' . $minimum . ' and ' . $maximum . '.';
            return null;
        }

        return $number;
    }

    public function date(
        string $value,
        string $label,
        ?string $minimum = null,
        ?string $maximum = null
    ): bool {
        if (!$this->required($value, $label)) {
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $this->errors[] = $label . ' must be a valid date.';
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));
        if (!checkdate($month, $day, $year)) {
            $this->errors[] = $label . ' must be a valid date.';
            return false;
        }

        if ($minimum !== null && $value < $minimum) {
            $this->errors[] = $label . ' cannot be before ' . $minimum . '.';
            return false;
        }
        if ($maximum !== null && $value > $maximum) {
            $this->errors[] = $label . ' cannot be after ' . $maximum . '.';
            return false;
        }

        return true;
    }

    public function reference(PDO $pdo, string $value, string $label, string $reference): ?int {
        if (!$this->required($value, $label)) {
            return null;
        }

        if (!preg_match('/^\d+$/', $value) || (int) $value < 1) {
            $this->errors[] = 'Select a valid ' . strtolower($label) . '.';
            return null;
        }

        if (!isset(self::REFERENCES[$reference])) {
            $this->errors[] = 'The selected ' . strtolower($label) . ' could not be checked.';
            return null;
        }

        [$table, $column] = self::REFERENCES[$reference];
        try {
            $stmt = $pdo->prepare("SELECT EXISTS (SELECT 1 FROM {$table} WHERE {$column} = :id)");
            $stmt->execute(['id' => (int) $value]);
            $exists = $stmt->fetchColumn();
        } catch (Throwable) {
            $this->errors[] = 'The selected ' . strtolower($label) . ' could not be verified. Please try again.';
            return null;
        }
        if (!($exists === true || $exists === 't' || $exists === '1')) {
            $this->errors[] = 'Select an existing ' . strtolower($label) . '.';
            return null;
        }

        return (int) $value;
    }

    private function normalizePhone(string $countryIso, string $value, ?array $original = null): ?string {
        if (!$this->required($value, 'Contact number')) {
            return null;
        }

        $countries = countryPhoneOptions();
        if (!$this->required($countryIso, 'Country code')) {
            return null;
        }
        if (!isset($countries[$countryIso])) {
            $this->errors[] = 'Select a valid country code.';
            return null;
        }

        $originalParts = $original !== null && isset($original['contact_number'])
            ? phoneFormParts((string) $original['contact_number'])
            : null;
        if ($originalParts !== null
            && $countryIso === $originalParts['country_iso']
            && trim($value) === trim($originalParts['national_number'])) {
            return (string) $original['contact_number'];
        }

        if (!preg_match('/^[0-9\s()+.-]+$/', $value)) {
            $this->errors[] = 'Contact number may contain only digits, spaces, parentheses, plus signs, and hyphens.';
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        $dialCode = $countries[$countryIso]['dial_code'];
        if (strlen($digits) > strlen($dialCode) && str_starts_with($digits, $dialCode)) {
            $digits = substr($digits, strlen($dialCode));
        }

        $trunkPrefix = $countries[$countryIso]['trunk'];
        if ($trunkPrefix !== '' && str_starts_with($digits, $trunkPrefix)) {
            $digits = substr($digits, strlen($trunkPrefix));
        }

        if ($countryIso === 'PH' && !preg_match('/^9[0-9]{9}$/', $digits)) {
            $this->errors[] = 'Philippine mobile numbers must have 10 digits and start with 9 after the +63 prefix.';
            return null;
        }

        if ($countryIso === 'US' && (strlen($digits) !== 10 || !preg_match('/^[2-9][0-9]{2}[2-9][0-9]{6}$/', $digits))) {
            $this->errors[] = 'United States numbers must have a valid 10-digit area code and subscriber number.';
            return null;
        }

        $totalDigits = strlen($dialCode) + strlen($digits);
        if ($digits === '' || $totalDigits < 8 || $totalDigits > 15) {
            $this->errors[] = 'Contact number must be 8 to 15 digits including its country code.';
            return null;
        }

        return '+' . $dialCode . $digits;
    }

    public function validateFarmer(array $values, ?array $original = null): array {
        $this->text($values['first_name'], 'First name', 2, 50, "/^[\p{L} .'-]+$/u", 'First name may contain only letters, spaces, periods, apostrophes, and hyphens.');
        $this->text($values['last_name'], 'Last name', 2, 50, "/^[\p{L} .'-]+$/u", 'Last name may contain only letters, spaces, periods, apostrophes, and hyphens.');
        $phone = $this->normalizePhone(
            $values['country_iso'],
            $values['contact_number'],
            $original
        );
        $this->text($values['address'], 'Address', 5, 150);
        $this->date($values['date_registered'], 'Registration date', null, date('Y-m-d'));
        $values['contact_number'] = $phone ?? '';
        return $values;
    }

    public function validateFarmerDuplicate(Farmer $farmerModel, array $values, ?int $excludeFarmerId = null): bool {
        if (!$farmerModel->hasDuplicate(
            $values['first_name'],
            $values['last_name'],
            $values['address'],
            $excludeFarmerId
        )) {
            return true;
        }

        $this->errors[] = 'A farmer with the same name and address already exists. Review the existing record before adding another.';
        return false;
    }

    public function validateFarm(PDO $pdo, array $values): array {
        $values['farmer_id'] = $this->reference($pdo, $values['farmer_id'], 'Farmer', 'farmer');
        $values['soil_type_id'] = $this->reference($pdo, $values['soil_type_id'], 'Soil type', 'soil_type');
        $values['irrigation_type_id'] = $this->reference($pdo, $values['irrigation_type_id'], 'Irrigation type', 'irrigation_type');
        $this->text($values['farm_name'], 'Farm name', 2, 100);
        $this->text($values['location'], 'Location', 2, 150);
        $values['area_hectares'] = $this->decimal($values['area_hectares'], 'Area (hectares)', 0.01, 10000);
        return $values;
    }

    public function validateCrop(PDO $pdo, array $values): array {
        $this->text($values['crop_name'], 'Crop name', 2, 80, '/^[\p{L} -]+$/u', 'Crop name may contain only letters, spaces, and hyphens.');
        $this->text($values['variety'], 'Variety name', 1, 80);
        $values['growing_period_days'] = $this->integer($values['growing_period_days'], 'Growing period', 1, 730);
        $values['category_id'] = $this->reference($pdo, $values['category_id'], 'Crop category', 'crop_category');
        $values['season_id'] = $this->reference($pdo, $values['season_id'], 'Recommended season', 'season');
        return $values;
    }

    public function validatePlanting(PDO $pdo, array $values, ?array $original = null): array {
        $values['farm_id'] = $this->reference($pdo, $values['farm_id'], 'Farm', 'farm');
        $values['variety_id'] = $this->reference($pdo, $values['variety_id'], 'Crop variety', 'variety');
        $values['status_id'] = $this->reference($pdo, $values['status_id'], 'Planting status', 'planting_status');
        $plantingDateValid = $this->date($values['planting_date'], 'Planting date');
        $expectedDateValid = $this->date($values['expected_harvest_date'], 'Expected harvest date');
        if ($plantingDateValid && $expectedDateValid && $values['expected_harvest_date'] < $values['planting_date']) {
            $this->errors[] = 'Expected harvest date must be on or after planting date.';
        }
        $values['area_planted'] = $this->decimal($values['area_planted'], 'Area planted', 0.01, 999999.99);
        $values['seed_quantity'] = $this->decimal($values['seed_quantity'], 'Seed quantity', 0.01, 999999.99);

        $unchangedExistingArea = $original !== null
            && $values['farm_id'] !== null
            && $values['area_planted'] !== null
            && (int) $values['farm_id'] === (int) ($original['farm_id'] ?? 0)
            && (float) $values['area_planted'] === (float) ($original['area_planted'] ?? -1);

        if (!$unchangedExistingArea && $values['farm_id'] !== null && $values['area_planted'] !== null) {
            try {
                $stmt = $pdo->prepare('SELECT area_hectares FROM farm WHERE farm_id = :farm_id');
                $stmt->execute(['farm_id' => $values['farm_id']]);
                $farmArea = $stmt->fetchColumn();
                if ($farmArea !== false && $values['area_planted'] > (float) $farmArea) {
                    $this->errors[] = 'Area planted cannot exceed the selected farm area (' . $farmArea . ' hectares).';
                }
            } catch (Throwable) {
                $this->errors[] = 'Farm area could not be verified. Please try again.';
            }
        }
        return $values;
    }

    public function validateHarvest(PDO $pdo, array $values): array {
        $values['planting_id'] = $this->reference($pdo, $values['planting_id'], 'Planting record', 'planting');
        $values['quality_grade_id'] = $this->reference($pdo, $values['quality_grade_id'], 'Quality grade', 'quality_grade');
        $values['remark_id'] = $this->reference($pdo, $values['remark_id'], 'Harvest remark', 'harvest_remark');
        $harvestDateValid = $this->date($values['harvest_date'], 'Harvest date', null, date('Y-m-d'));
        $values['quantity_kg'] = $this->decimal($values['quantity_kg'], 'Quantity (kg)', 0.01, 99999999.99);
        $values['selling_price_per_kg'] = $this->decimal($values['selling_price_per_kg'], 'Selling price per kg', 0, 99999999.99);

        if ($values['planting_id'] !== null && $harvestDateValid) {
            try {
                $stmt = $pdo->prepare('SELECT planting_date FROM planting WHERE planting_id = :planting_id');
                $stmt->execute(['planting_id' => $values['planting_id']]);
                $plantingDate = $stmt->fetchColumn();
                if ($plantingDate !== false && $values['harvest_date'] < $plantingDate) {
                    $this->errors[] = 'Harvest date cannot be before planting date.';
                }
            } catch (Throwable) {
                $this->errors[] = 'Planting date could not be verified. Please try again.';
            }
        }
        return $values;
    }

    public function validateLogin(string $username, string $password): bool {
        $usernameValid = $this->required($username, 'Username');
        $passwordValid = $this->required(trim($password), 'Password');
        return $usernameValid && $passwordValid;
    }

    public function addError(string $message): void {
        $this->errors[] = $message;
    }

    public function errors(): array {
        return array_values(array_unique($this->errors));
    }
}
