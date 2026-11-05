<?php
class Harvest {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        int $qualityGradeId = 0,
        string $sort = 'harvest_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'harvest_id' => 'h.harvest_id',
            'farmer_name' => 'fm.last_name',
            'farm_name' => 'f.farm_name',
            'crop_name' => 'c.crop_name',
            'harvest_date' => 'h.harvest_date',
            'quantity_kg' => 'h.quantity_kg',
            'selling_price_per_kg' => 'h.selling_price_per_kg',
            'quality_grade' => 'q.quality_grade_name',
            'remark' => 'r.remark_description'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'h.harvest_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT h.harvest_id, h.planting_id, h.harvest_date, h.quantity_kg,
                       h.selling_price_per_kg, h.quality_grade_id, h.remark_id,
                       fm.first_name || ' ' || fm.last_name AS farmer_name,
                       f.farm_name, c.crop_name, c.variety,
                       q.quality_grade_name, r.remark_description
                FROM harvest h
                INNER JOIN planting p ON h.planting_id = p.planting_id
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                INNER JOIN harvest_quality_grades q ON h.quality_grade_id = q.quality_grade_id
                INNER JOIN harvest_remarks r ON h.remark_id = r.remark_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
                OR f.farm_name ILIKE :search_farm
                OR c.crop_name ILIKE :search_crop
                OR c.variety ILIKE :search_variety
                OR r.remark_description ILIKE :search_remark
            )";
            $searchValue = '%' . $search . '%';
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
            $params['search_farm'] = $searchValue;
            $params['search_crop'] = $searchValue;
            $params['search_variety'] = $searchValue;
            $params['search_remark'] = $searchValue;
        }

        if ($qualityGradeId > 0) {
            $sql .= ' AND h.quality_grade_id = :quality_grade_id';
            $params['quality_grade_id'] = $qualityGradeId;
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, h.harvest_id ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === 'quality_grade_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', int $qualityGradeId = 0): int {
        $sql = "SELECT COUNT(*)
                FROM harvest h
                INNER JOIN planting p ON h.planting_id = p.planting_id
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                INNER JOIN harvest_remarks r ON h.remark_id = r.remark_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
                OR f.farm_name ILIKE :search_farm
                OR c.crop_name ILIKE :search_crop
                OR c.variety ILIKE :search_variety
                OR r.remark_description ILIKE :search_remark
            )";
            $searchValue = '%' . $search . '%';
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
            $params['search_farm'] = $searchValue;
            $params['search_crop'] = $searchValue;
            $params['search_variety'] = $searchValue;
            $params['search_remark'] = $searchValue;
        }

        if ($qualityGradeId > 0) {
            $sql .= ' AND h.quality_grade_id = :quality_grade_id';
            $params['quality_grade_id'] = $qualityGradeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $harvest_id): ?array {
        $sql = "SELECT h.harvest_id, h.planting_id, h.harvest_date, h.quantity_kg,
                       h.selling_price_per_kg, h.quality_grade_id, h.remark_id,
                       p.planting_date, p.expected_harvest_date,
                       fm.first_name || ' ' || fm.last_name AS farmer_name,
                       f.farm_name, c.crop_name, c.variety,
                       q.quality_grade_name, r.remark_description,
                       h.created_at, h.updated_at
                FROM harvest h
                INNER JOIN planting p ON h.planting_id = p.planting_id
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                INNER JOIN harvest_quality_grades q ON h.quality_grade_id = q.quality_grade_id
                INNER JOIN harvest_remarks r ON h.remark_id = r.remark_id
                WHERE h.harvest_id = :harvest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["harvest_id" => $harvest_id]);

        $harvest = $stmt->fetch();
        return $harvest ?: null;
    }

    public function getPlantingById(int $plantingId): ?array {
        $sql = "SELECT p.planting_id, p.planting_date, p.expected_harvest_date,
                       f.farm_name, c.crop_name, c.variety,
                       fm.first_name || ' ' || fm.last_name AS farmer_name
                FROM planting p
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                WHERE p.planting_id = :planting_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["planting_id" => $plantingId]);

        $planting = $stmt->fetch();
        return $planting ?: null;
    }

    public function getPlantings(): array {
        $sql = "SELECT p.planting_id, p.planting_date,
                       f.farm_name, c.crop_name, c.variety,
                       fm.first_name || ' ' || fm.last_name AS farmer_name
                FROM planting p
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                ORDER BY p.planting_date DESC, p.planting_id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function hasValidReferences(int $plantingId, int $qualityGradeId, int $remarkId): bool {
        $sql = "SELECT
                    EXISTS (SELECT 1 FROM planting WHERE planting_id = :planting_id)
                    AND EXISTS (SELECT 1 FROM harvest_quality_grades WHERE quality_grade_id = :quality_grade_id)
                    AND EXISTS (SELECT 1 FROM harvest_remarks WHERE remark_id = :remark_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "planting_id" => $plantingId,
            "quality_grade_id" => $qualityGradeId,
            "remark_id" => $remarkId
        ]);

        $isValid = $stmt->fetchColumn();
        return $isValid === true || $isValid === 't' || $isValid === '1';
    }

    public function create(
        int $plantingId,
        string $harvestDate,
        float $quantityKg,
        float $sellingPricePerKg,
        int $qualityGradeId,
        int $remarkId
    ): int {
        $sql = "INSERT INTO harvest (
                    planting_id, harvest_date, quantity_kg, selling_price_per_kg,
                    quality_grade_id, remark_id
                )
                SELECT :planting_id, :harvest_date, :quantity_kg, :selling_price_per_kg,
                       q.quality_grade_id, r.remark_id
                FROM harvest_quality_grades q
                CROSS JOIN harvest_remarks r
                WHERE q.quality_grade_id = :quality_grade_id
                  AND r.remark_id = :remark_id
                RETURNING harvest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "planting_id" => $plantingId,
            "harvest_date" => $harvestDate,
            "quantity_kg" => $quantityKg,
            "selling_price_per_kg" => $sellingPricePerKg,
            "quality_grade_id" => $qualityGradeId,
            "remark_id" => $remarkId
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function update(
        int $harvestId,
        int $plantingId,
        string $harvestDate,
        float $quantityKg,
        float $sellingPricePerKg,
        int $qualityGradeId,
        int $remarkId
    ): bool {
        $sql = "UPDATE harvest AS h
                SET planting_id = :planting_id,
                    harvest_date = :harvest_date,
                    quantity_kg = :quantity_kg,
                    selling_price_per_kg = :selling_price_per_kg,
                    quality_grade_id = q.quality_grade_id,
                    remark_id = r.remark_id,
                    updated_at = CURRENT_TIMESTAMP
                FROM harvest_quality_grades q, harvest_remarks r
                WHERE h.harvest_id = :harvest_id
                  AND q.quality_grade_id = :quality_grade_id
                  AND r.remark_id = :remark_id
                RETURNING h.harvest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "planting_id" => $plantingId,
            "harvest_date" => $harvestDate,
            "quantity_kg" => $quantityKg,
            "selling_price_per_kg" => $sellingPricePerKg,
            "quality_grade_id" => $qualityGradeId,
            "remark_id" => $remarkId,
            "harvest_id" => $harvestId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(int $harvest_id): bool {
        $sql = "DELETE FROM harvest WHERE harvest_id = :harvest_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(["harvest_id" => $harvest_id]);
    }
}
