<?php
class Crop {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        int $categoryId = 0,
        string $sort = 'crop_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'crop_id' => 'c.crop_id',
            'crop_name' => 'c.crop_name',
            'variety' => 'c.variety',
            'crop_category' => 'cc.crop_category_name',
            'growing_period_days' => 'c.growing_period_days',
            'recommended_season' => 'rs.recommended_season_name'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'c.crop_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT c.crop_id, c.crop_name, c.variety, c.growing_period_days,
                       c.crop_category_id, c.recommended_season_id,
                       cc.crop_category_name, rs.recommended_season_name
                FROM crop c
                INNER JOIN crop_categories cc ON c.crop_category_id = cc.crop_category_id
                INNER JOIN recommended_seasons rs ON c.recommended_season_id = rs.recommended_season_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                c.crop_name ILIKE :search_name
                OR c.variety ILIKE :search_variety
            )";
            $searchValue = '%' . $search . '%';
            $params['search_name'] = $searchValue;
            $params['search_variety'] = $searchValue;
        }

        if ($categoryId > 0) {
            $sql .= ' AND c.crop_category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, c.crop_id ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === 'category_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', int $categoryId = 0): int {
        $sql = "SELECT COUNT(*)
                FROM crop c
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.crop_name ILIKE :search_name OR c.variety ILIKE :search_variety)";
            $searchValue = '%' . $search . '%';
            $params['search_name'] = $searchValue;
            $params['search_variety'] = $searchValue;
        }

        if ($categoryId > 0) {
            $sql .= ' AND c.crop_category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $crop_id): ?array {
        $sql = "SELECT c.crop_id, c.crop_name, c.variety, c.growing_period_days,
                       c.crop_category_id, c.recommended_season_id,
                       cc.crop_category_name, rs.recommended_season_name,
                       c.created_at, c.updated_at
                FROM crop c
                INNER JOIN crop_categories cc ON c.crop_category_id = cc.crop_category_id
                INNER JOIN recommended_seasons rs ON c.recommended_season_id = rs.recommended_season_id
                WHERE c.crop_id = :crop_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["crop_id" => $crop_id]);

        $crop = $stmt->fetch();
        return $crop ?: null;
    }

    public function getCategories(): array {
        $sql = "SELECT crop_category_id, crop_category_name
                FROM crop_categories
                ORDER BY crop_category_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getRecommendedSeasons(): array {
        $sql = "SELECT recommended_season_id, recommended_season_name
                FROM recommended_seasons
                ORDER BY recommended_season_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function hasValidReferences(int $categoryId, int $seasonId): bool {
        $sql = "SELECT
                    EXISTS (SELECT 1 FROM crop_categories WHERE crop_category_id = :category_id)
                    AND EXISTS (SELECT 1 FROM recommended_seasons WHERE recommended_season_id = :season_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "category_id" => $categoryId,
            "season_id" => $seasonId
        ]);

        $isValid = $stmt->fetchColumn();
        return $isValid === true || $isValid === 't' || $isValid === '1';
    }

    public function create(
        string $crop_name,
        string $variety,
        int $growing_period_days,
        int $categoryId,
        int $seasonId
    ): int {
        $sql = "INSERT INTO crop (
                    crop_name, variety, growing_period_days,
                    crop_category_id, recommended_season_id
                )
                SELECT :crop_name, :variety, :growing_period_days,
                       cc.crop_category_id, rs.recommended_season_id
                FROM crop_categories cc
                CROSS JOIN recommended_seasons rs
                WHERE cc.crop_category_id = :category_id
                  AND rs.recommended_season_id = :season_id
                RETURNING crop_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "crop_name" => $crop_name,
            "variety" => $variety,
            "growing_period_days" => $growing_period_days,
            "category_id" => $categoryId,
            "season_id" => $seasonId
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function update(
        int $crop_id,
        string $crop_name,
        string $variety,
        int $growing_period_days,
        int $categoryId,
        int $seasonId
    ): bool {
        $sql = "UPDATE crop AS c
                SET crop_name = :crop_name,
                    variety = :variety,
                    growing_period_days = :growing_period_days,
                    crop_category_id = cc.crop_category_id,
                    recommended_season_id = rs.recommended_season_id,
                    updated_at = CURRENT_TIMESTAMP
                FROM crop_categories cc, recommended_seasons rs
                WHERE c.crop_id = :crop_id
                  AND cc.crop_category_id = :category_id
                  AND rs.recommended_season_id = :season_id
                RETURNING c.crop_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "crop_name" => $crop_name,
            "variety" => $variety,
            "growing_period_days" => $growing_period_days,
            "category_id" => $categoryId,
            "season_id" => $seasonId,
            "crop_id" => $crop_id
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(int $crop_id): bool {
        $sql = "DELETE FROM crop WHERE crop_id = :crop_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(["crop_id" => $crop_id]);
    }
}
