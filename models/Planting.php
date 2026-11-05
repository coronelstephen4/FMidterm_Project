<?php
class Planting {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        int $statusId = 0,
        string $sort = 'planting_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'planting_id' => 'p.planting_id',
            'farmer_name' => 'fm.last_name',
            'farm_name' => 'f.farm_name',
            'crop_name' => 'c.crop_name',
            'planting_date' => 'p.planting_date',
            'area_planted' => 'p.area_planted',
            'expected_harvest_date' => 'p.expected_harvest_date',
            'status' => 'ps.status_name'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'p.planting_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT p.planting_id, p.planting_date, p.area_planted, p.seed_quantity,
                       p.expected_harvest_date, p.farm_id, p.crop_id, p.status_id,
                       f.farm_name, fm.first_name || ' ' || fm.last_name AS farmer_name,
                       c.crop_name, c.variety, ps.status_name
                FROM planting p
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                INNER JOIN planting_statuses ps ON p.status_id = ps.status_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                f.farm_name ILIKE :search_farm
                OR c.crop_name ILIKE :search_crop
                OR c.variety ILIKE :search_variety
                OR fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
            )";
            $searchValue = '%' . $search . '%';
            $params['search_farm'] = $searchValue;
            $params['search_crop'] = $searchValue;
            $params['search_variety'] = $searchValue;
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
        }

        if ($statusId > 0) {
            $sql .= ' AND p.status_id = :status_id';
            $params['status_id'] = $statusId;
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, p.planting_id ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === 'status_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', int $statusId = 0): int {
        $sql = "SELECT COUNT(*)
                FROM planting p
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                f.farm_name ILIKE :search_farm
                OR c.crop_name ILIKE :search_crop
                OR c.variety ILIKE :search_variety
                OR fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
            )";
            $searchValue = '%' . $search . '%';
            $params['search_farm'] = $searchValue;
            $params['search_crop'] = $searchValue;
            $params['search_variety'] = $searchValue;
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
        }

        if ($statusId > 0) {
            $sql .= ' AND p.status_id = :status_id';
            $params['status_id'] = $statusId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $planting_id): ?array {
        $sql = "SELECT p.planting_id, p.farm_id, p.crop_id, p.planting_date,
                       p.area_planted, p.seed_quantity, p.expected_harvest_date, p.status_id,
                       f.farm_name, fm.first_name || ' ' || fm.last_name AS farmer_name,
                       c.crop_name, c.variety, ps.status_name,
                       p.created_at, p.updated_at
                FROM planting p
                INNER JOIN farm f ON p.farm_id = f.farm_id
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN crop c ON p.crop_id = c.crop_id
                INNER JOIN planting_statuses ps ON p.status_id = ps.status_id
                WHERE p.planting_id = :planting_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["planting_id" => $planting_id]);

        $planting = $stmt->fetch();
        return $planting ?: null;
    }

    public function getFarms(): array {
        $sql = "SELECT f.farm_id, f.farm_name,
                       fm.first_name || ' ' || fm.last_name AS farmer_name
                FROM farm f
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                ORDER BY f.farm_name, f.farm_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCrops(): array {
        $sql = "SELECT crop_id, crop_name, variety
                FROM crop
                ORDER BY crop_name, variety, crop_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getStatuses(): array {
        $sql = "SELECT status_id, status_name
                FROM planting_statuses
                ORDER BY status_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function hasValidReferences(int $farmId, int $cropId, int $statusId): bool {
        $sql = "SELECT
                    EXISTS (SELECT 1 FROM farm WHERE farm_id = :farm_id)
                    AND EXISTS (SELECT 1 FROM crop WHERE crop_id = :crop_id)
                    AND EXISTS (SELECT 1 FROM planting_statuses WHERE status_id = :status_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farm_id" => $farmId,
            "crop_id" => $cropId,
            "status_id" => $statusId
        ]);

        $isValid = $stmt->fetchColumn();
        return $isValid === true || $isValid === 't' || $isValid === '1';
    }

    public function create(
        int $farmId,
        int $cropId,
        string $plantingDate,
        float $areaPlanted,
        float $seedQuantity,
        string $expectedHarvestDate,
        int $statusId
    ): int {
        $sql = "INSERT INTO planting (
                    farm_id, crop_id, planting_date, area_planted, seed_quantity,
                    expected_harvest_date, status_id
                )
                SELECT :farm_id, :crop_id, :planting_date, :area_planted, :seed_quantity,
                       :expected_harvest_date, s.status_id
                FROM planting_statuses s
                WHERE s.status_id = :status_id
                RETURNING planting_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farm_id" => $farmId,
            "crop_id" => $cropId,
            "planting_date" => $plantingDate,
            "area_planted" => $areaPlanted,
            "seed_quantity" => $seedQuantity,
            "expected_harvest_date" => $expectedHarvestDate,
            "status_id" => $statusId
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function update(
        int $plantingId,
        int $farmId,
        int $cropId,
        string $plantingDate,
        float $areaPlanted,
        float $seedQuantity,
        string $expectedHarvestDate,
        int $statusId
    ): bool {
        $sql = "UPDATE planting AS p
                SET farm_id = :farm_id,
                    crop_id = :crop_id,
                    planting_date = :planting_date,
                    area_planted = :area_planted,
                    seed_quantity = :seed_quantity,
                    expected_harvest_date = :expected_harvest_date,
                    status_id = s.status_id,
                    updated_at = CURRENT_TIMESTAMP
                FROM planting_statuses s
                WHERE p.planting_id = :planting_id
                  AND s.status_id = :status_id
                RETURNING p.planting_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farm_id" => $farmId,
            "crop_id" => $cropId,
            "planting_date" => $plantingDate,
            "area_planted" => $areaPlanted,
            "seed_quantity" => $seedQuantity,
            "expected_harvest_date" => $expectedHarvestDate,
            "status_id" => $statusId,
            "planting_id" => $plantingId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(int $planting_id): bool {
        $sql = "DELETE FROM planting WHERE planting_id = :planting_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(["planting_id" => $planting_id]);
    }
}
