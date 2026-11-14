<?php
class Farm {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        int $irrigationTypeId = 0,
        string $sort = 'farm_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'farm_id' => 'f.farm_id',
            'farm_name' => 'f.farm_name',
            'farmer_name' => 'fm.last_name',
            'location' => 'f.location',
            'area_hectares' => 'f.area_hectares',
            'irrigation_type' => 'it.irrigation_type_name'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'f.farm_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT f.farm_id, f.farm_name, f.location, f.area_hectares,
                       f.farmer_id, f.irrigation_type_id, f.soil_type_id,
                       fm.first_name || ' ' || fm.last_name AS farmer_name,
                       it.irrigation_type_name, st.soil_type_name
                FROM farm f
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN irrigation_types it ON f.irrigation_type_id = it.irrigation_type_id
                INNER JOIN soil_types st ON f.soil_type_id = st.soil_type_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                f.farm_name ILIKE :search_name
                OR f.location ILIKE :search_location
                OR fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
            )";
            $searchValue = '%' . $search . '%';
            $params['search_name'] = $searchValue;
            $params['search_location'] = $searchValue;
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
        }

        if ($irrigationTypeId > 0) {
            $sql .= ' AND f.irrigation_type_id = :irrigation_type_id';
            $params['irrigation_type_id'] = $irrigationTypeId;
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, f.farm_id ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === 'irrigation_type_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', int $irrigationTypeId = 0): int {
        $sql = "SELECT COUNT(*)
                FROM farm f
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                f.farm_name ILIKE :search_name
                OR f.location ILIKE :search_location
                OR fm.first_name ILIKE :search_first_name
                OR fm.last_name ILIKE :search_last_name
            )";
            $searchValue = '%' . $search . '%';
            $params['search_name'] = $searchValue;
            $params['search_location'] = $searchValue;
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
        }

        if ($irrigationTypeId > 0) {
            $sql .= ' AND f.irrigation_type_id = :irrigation_type_id';
            $params['irrigation_type_id'] = $irrigationTypeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $farm_id): ?array {
        $sql = "SELECT f.farm_id, f.farmer_id, f.farm_name, f.location, f.area_hectares,
                       f.soil_type_id, f.irrigation_type_id,
                       fm.first_name || ' ' || fm.last_name AS farmer_name,
                       it.irrigation_type_name, st.soil_type_name,
                       f.created_at, f.updated_at
                FROM farm f
                INNER JOIN farmer fm ON f.farmer_id = fm.farmer_id
                INNER JOIN irrigation_types it ON f.irrigation_type_id = it.irrigation_type_id
                INNER JOIN soil_types st ON f.soil_type_id = st.soil_type_id
                WHERE f.farm_id = :farm_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["farm_id" => $farm_id]);

        $farm = $stmt->fetch();
        return $farm ?: null;
    }

    public function getFarmers(): array {
        $sql = "SELECT farmer_id, first_name, last_name
                FROM farmer
                ORDER BY last_name, first_name, farmer_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getIrrigationTypes(): array {
        $sql = "SELECT irrigation_type_id, irrigation_type_name
                FROM irrigation_types
                ORDER BY irrigation_type_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getSoilTypes(): array {
        $sql = "SELECT soil_type_id, soil_type_name
                FROM soil_types
                ORDER BY soil_type_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function hasValidReferences(int $farmer_id, int $irrigation_type_id, int $soil_type_id): bool {
        $sql = "SELECT
                    EXISTS (SELECT 1 FROM farmer WHERE farmer_id = :farmer_id)
                    AND EXISTS (SELECT 1 FROM irrigation_types WHERE irrigation_type_id = :irrigation_type_id)
                    AND EXISTS (SELECT 1 FROM soil_types WHERE soil_type_id = :soil_type_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farmer_id" => $farmer_id,
            "irrigation_type_id" => $irrigation_type_id,
            "soil_type_id" => $soil_type_id
        ]);

        $isValid = $stmt->fetchColumn();
        return $isValid === true || $isValid === 't' || $isValid === '1';
    }

    public function create(
        int $farmer_id,
        string $farm_name,
        string $location,
        float $area_hectares,
        int $soil_type_id,
        int $irrigation_type_id
    ): int {
        $sql = "INSERT INTO farm (
                    farmer_id, farm_name, location, area_hectares,
                    soil_type_id, irrigation_type_id
                )
                SELECT :farmer_id, :farm_name, :location, :area_hectares,
                       st.soil_type_id, it.irrigation_type_id
                FROM soil_types st
                CROSS JOIN irrigation_types it
                WHERE st.soil_type_id = :soil_type_id
                  AND it.irrigation_type_id = :irrigation_type_id
                RETURNING farm_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farmer_id" => $farmer_id,
            "farm_name" => $farm_name,
            "location" => $location,
            "area_hectares" => $area_hectares,
            "soil_type_id" => $soil_type_id,
            "irrigation_type_id" => $irrigation_type_id
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function update(
        int $farm_id,
        int $farmer_id,
        string $farm_name,
        string $location,
        float $area_hectares,
        int $soil_type_id,
        int $irrigation_type_id
    ): bool {
        $sql = "UPDATE farm AS f
                SET farmer_id = :farmer_id,
                    farm_name = :farm_name,
                    location = :location,
                    area_hectares = :area_hectares,
                    soil_type_id = st.soil_type_id,
                    irrigation_type_id = it.irrigation_type_id,
                    updated_at = CURRENT_TIMESTAMP
                FROM soil_types st, irrigation_types it
                WHERE f.farm_id = :farm_id
                  AND st.soil_type_id = :soil_type_id
                  AND it.irrigation_type_id = :irrigation_type_id
                RETURNING f.farm_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "farmer_id" => $farmer_id,
            "farm_name" => $farm_name,
            "location" => $location,
            "area_hectares" => $area_hectares,
            "soil_type_id" => $soil_type_id,
            "irrigation_type_id" => $irrigation_type_id,
            "farm_id" => $farm_id
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(int $farm_id): bool {
        $sql = "DELETE FROM farm WHERE farm_id = :farm_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(["farm_id" => $farm_id]);
    }
}
