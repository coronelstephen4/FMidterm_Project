<?php
class DuplicateChecker {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findFarmer(
        string $firstName,
        string $lastName,
        string $address,
        ?int $excludeId = null
    ): ?array {
        $sql = <<<'SQL'
            SELECT farmer_id AS id,
                   first_name || ' ' || last_name AS label
            FROM farmer
            WHERE lower(btrim(first_name)) = lower(btrim(:first_name))
              AND lower(btrim(last_name)) = lower(btrim(:last_name))
              AND lower(regexp_replace(btrim(address), '\s+', ' ', 'g'))
                  = lower(regexp_replace(btrim(:address), '\s+', ' ', 'g'))
              AND farmer_id <> COALESCE(:exclude_id, -1)
            LIMIT 1
            SQL;

        return $this->findMatch($sql, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'exclude_id' => $excludeId
        ]);
    }

    public function findFarm(
        int $farmerId,
        string $farmName,
        string $location,
        float $areaHectares,
        int $irrigationTypeId,
        int $soilTypeId,
        ?int $excludeId = null
    ): ?array {
        $sql = <<<'SQL'
            SELECT farm_id AS id, farm_name AS label
            FROM farm
            WHERE farmer_id = :farmer_id
              AND lower(btrim(farm_name)) = lower(btrim(:farm_name))
              AND lower(regexp_replace(btrim(location), '\s+', ' ', 'g'))
                  = lower(regexp_replace(btrim(:location), '\s+', ' ', 'g'))
              AND area_hectares = :area_hectares
              AND irrigation_type_id = :irrigation_type_id
              AND soil_type_id = :soil_type_id
              AND farm_id <> COALESCE(:exclude_id, -1)
            LIMIT 1
            SQL;

        return $this->findMatch($sql, [
            'farmer_id' => $farmerId,
            'farm_name' => $farmName,
            'location' => $location,
            'area_hectares' => $areaHectares,
            'irrigation_type_id' => $irrigationTypeId,
            'soil_type_id' => $soilTypeId,
            'exclude_id' => $excludeId
        ]);
    }

    public function findPlanting(
        int $farmId,
        int $varietyId,
        string $plantingDate,
        ?int $excludeId = null
    ): ?array {
        $sql = <<<'SQL'
            SELECT planting_id AS id, 'Planting record' AS label
            FROM planting
            WHERE farm_id = :farm_id
              AND variety_id = :variety_id
              AND planting_date = :planting_date
              AND planting_id <> COALESCE(:exclude_id, -1)
            LIMIT 1
            SQL;

        return $this->findMatch($sql, [
            'farm_id' => $farmId,
            'variety_id' => $varietyId,
            'planting_date' => $plantingDate,
            'exclude_id' => $excludeId
        ]);
    }

    public function findHarvest(
        int $plantingId,
        string $harvestDate,
        ?int $excludeId = null
    ): ?array {
        $sql = <<<'SQL'
            SELECT harvest_id AS id, 'Harvest record' AS label
            FROM harvest
            WHERE planting_id = :planting_id
              AND harvest_date = :harvest_date
              AND harvest_id <> COALESCE(:exclude_id, -1)
            LIMIT 1
            SQL;

        return $this->findMatch($sql, [
            'planting_id' => $plantingId,
            'harvest_date' => $harvestDate,
            'exclude_id' => $excludeId
        ]);
    }

    public function findCropVariety(
        string $cropName,
        string $varietyName,
        ?int $excludeVarietyId = null
    ): ?array {
        $sql = <<<'SQL'
            SELECT cv.variety_id AS id, cv.variety_name AS label
            FROM crop_variety cv
            INNER JOIN crop c ON c.crop_id = cv.crop_id
            WHERE lower(btrim(c.crop_name)) = lower(btrim(:crop_name))
              AND lower(btrim(cv.variety_name)) = lower(btrim(:variety_name))
              AND cv.variety_id <> COALESCE(:exclude_id, -1)
            LIMIT 1
            SQL;

        return $this->findMatch($sql, [
            'crop_name' => $cropName,
            'variety_name' => $varietyName,
            'exclude_id' => $excludeVarietyId
        ]);
    }

    public static function isUniqueViolation(Throwable $error): bool {
        return $error instanceof PDOException
            && ($error->errorInfo[0] ?? $error->getCode()) === '23505';
    }

    public static function notice(string $type, ?array $match): array {
        $id = $match['id'] ?? null;
        $messages = [
            'Farmer' => $id
                ? "A farmer with the same name and address already exists (Farmer #{$id}). Review the existing record before adding another."
                : 'A farmer with the same name and address already exists. Review the existing record before adding another.',
            'Farm' => $id
                ? "This farm already exists for the selected farmer with identical details (Farm #{$id})."
                : 'This farm already exists for the selected farmer with identical details.',
            'Planting' => $id
                ? "A planting for this farm, variety and date already exists (Planting #{$id})."
                : 'A planting for this farm, variety and date already exists.',
            'Harvest' => $id
                ? "A harvest for this planting and date already exists (Harvest #{$id})."
                : 'A harvest for this planting and date already exists.',
            'Crop variety' => $id
                ? "This variety name is already registered for this crop (Variety #{$id})."
                : 'This variety name is already registered for this crop.'
        ];

        return [
            'type' => $type,
            'id' => $id,
            'message' => $messages[$type] ?? 'An identical record already exists.'
        ];
    }

    private function findMatch(string $sql, array $values): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        $match = $stmt->fetch();

        if (!$match) {
            return null;
        }

        return [
            'id' => (int) $match['id'],
            'label' => (string) $match['label']
        ];
    }
}
