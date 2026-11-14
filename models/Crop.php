<?php
class Crop {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        int $categoryId = 0,
        string $sort = 'variety_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'variety_id' => 'cv.variety_id',
            'crop_name' => 'c.crop_name',
            'variety' => 'cv.variety_name',
            'crop_category' => 'cc.crop_category_name',
            'growing_period_days' => 'cv.growing_period_days',
            'recommended_season' => 'rs.recommended_season_name'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'cv.variety_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT cv.variety_id, c.crop_id, c.crop_name,
                       cv.variety_name AS variety, cv.growing_period_days,
                       c.crop_category_id, cv.recommended_season_id,
                       cc.crop_category_name, rs.recommended_season_name
                FROM crop_variety cv
                INNER JOIN crop c ON c.crop_id = cv.crop_id
                INNER JOIN crop_categories cc ON cc.crop_category_id = c.crop_category_id
                INNER JOIN recommended_seasons rs ON rs.recommended_season_id = cv.recommended_season_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (c.crop_name ILIKE :search_name OR cv.variety_name ILIKE :search_variety)';
            $searchValue = '%' . $search . '%';
            $params['search_name'] = $searchValue;
            $params['search_variety'] = $searchValue;
        }
        if ($categoryId > 0) {
            $sql .= ' AND c.crop_category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, cv.variety_id ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, $key === 'category_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', int $categoryId = 0): int {
        $sql = "SELECT COUNT(*)
                FROM crop_variety cv
                INNER JOIN crop c ON c.crop_id = cv.crop_id
                WHERE 1 = 1";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (c.crop_name ILIKE :search_name OR cv.variety_name ILIKE :search_variety)';
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

    public function getById(int $varietyId): ?array {
        $sql = "SELECT cv.variety_id, c.crop_id, c.crop_name,
                       cv.variety_name AS variety, cv.growing_period_days,
                       c.crop_category_id, cv.recommended_season_id,
                       cc.crop_category_name, rs.recommended_season_name,
                       cv.created_at, cv.updated_at
                FROM crop_variety cv
                INNER JOIN crop c ON c.crop_id = cv.crop_id
                INNER JOIN crop_categories cc ON cc.crop_category_id = c.crop_category_id
                INNER JOIN recommended_seasons rs ON rs.recommended_season_id = cv.recommended_season_id
                WHERE cv.variety_id = :variety_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['variety_id' => $varietyId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCategories(): array {
        return $this->pdo->query(
            'SELECT crop_category_id, crop_category_name FROM crop_categories ORDER BY crop_category_id'
        )->fetchAll();
    }

    public function getRecommendedSeasons(): array {
        return $this->pdo->query(
            'SELECT recommended_season_id, recommended_season_name FROM recommended_seasons ORDER BY recommended_season_id'
        )->fetchAll();
    }

    public function hasValidReferences(int $categoryId, int $seasonId): bool {
        $sql = "SELECT EXISTS (SELECT 1 FROM crop_categories WHERE crop_category_id = :category_id)
                AND EXISTS (SELECT 1 FROM recommended_seasons WHERE recommended_season_id = :season_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['category_id' => $categoryId, 'season_id' => $seasonId]);
        $exists = $stmt->fetchColumn();
        return $exists === true || $exists === 't' || $exists === '1';
    }

    public function create(
        string $cropName,
        string $variety,
        int $growingPeriodDays,
        int $categoryId,
        int $seasonId
    ): int {
        $this->pdo->beginTransaction();
        try {
            $find = $this->pdo->prepare(
                'SELECT crop_id, crop_category_id FROM crop WHERE lower(btrim(crop_name)) = lower(btrim(:crop_name)) FOR UPDATE'
            );
            $find->execute(['crop_name' => $cropName]);
            $crop = $find->fetch();

            if ($crop && (int) $crop['crop_category_id'] !== $categoryId) {
                throw new DomainException('This crop name already uses a different category.');
            }

            if ($crop) {
                $cropId = (int) $crop['crop_id'];
            } else {
                $insertCrop = $this->pdo->prepare(
                    'INSERT INTO crop (crop_name, crop_category_id) VALUES (:crop_name, :category_id) RETURNING crop_id'
                );
                $insertCrop->execute(['crop_name' => $cropName, 'category_id' => $categoryId]);
                $cropId = (int) $insertCrop->fetchColumn();
            }

            $insertVariety = $this->pdo->prepare(
                'INSERT INTO crop_variety (crop_id, variety_name, growing_period_days, recommended_season_id)
                 VALUES (:crop_id, :variety, :growing_period_days, :season_id) RETURNING variety_id'
            );
            $insertVariety->execute([
                'crop_id' => $cropId,
                'variety' => $variety,
                'growing_period_days' => $growingPeriodDays,
                'season_id' => $seasonId
            ]);
            $varietyId = (int) $insertVariety->fetchColumn();
            $this->pdo->commit();
            return $varietyId;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function update(
        int $varietyId,
        string $cropName,
        string $variety,
        int $growingPeriodDays,
        int $categoryId,
        int $seasonId
    ): bool {
        $this->pdo->beginTransaction();
        try {
            $updateCrop = $this->pdo->prepare(
                "UPDATE crop c
                 SET crop_name = :crop_name, crop_category_id = :category_id_set, updated_at = CURRENT_TIMESTAMP
                 WHERE c.crop_id = (SELECT crop_id FROM crop_variety WHERE variety_id = :variety_id)
                   AND EXISTS (SELECT 1 FROM crop_categories WHERE crop_category_id = :category_id_check)"
            );
            $updateCrop->execute([
                'crop_name' => $cropName,
                'category_id_set' => $categoryId,
                'category_id_check' => $categoryId,
                'variety_id' => $varietyId
            ]);
            if ($updateCrop->rowCount() < 1) {
                throw new RuntimeException('Crop variety was not found or category is invalid.');
            }

            $updateVariety = $this->pdo->prepare(
                'UPDATE crop_variety
                 SET variety_name = :variety, growing_period_days = :growing_period_days,
                     recommended_season_id = :season_id, updated_at = CURRENT_TIMESTAMP
                 WHERE variety_id = :variety_id
                   AND EXISTS (SELECT 1 FROM recommended_seasons WHERE recommended_season_id = :season_id_check)'
            );
            $updateVariety->execute([
                'variety' => $variety,
                'growing_period_days' => $growingPeriodDays,
                'season_id' => $seasonId,
                'season_id_check' => $seasonId,
                'variety_id' => $varietyId
            ]);
            if ($updateVariety->rowCount() < 1) {
                throw new RuntimeException('Crop variety was not updated.');
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function delete(int $varietyId): bool {
        $this->pdo->beginTransaction();
        try {
            $deleteVariety = $this->pdo->prepare(
                'DELETE FROM crop_variety WHERE variety_id = :variety_id RETURNING crop_id'
            );
            $deleteVariety->execute(['variety_id' => $varietyId]);
            $cropId = $deleteVariety->fetchColumn();
            if ($cropId === false) {
                $this->pdo->rollBack();
                return false;
            }

            $deleteCrop = $this->pdo->prepare(
                'DELETE FROM crop c WHERE c.crop_id = :crop_id
                 AND NOT EXISTS (SELECT 1 FROM crop_variety cv WHERE cv.crop_id = c.crop_id)'
            );
            $deleteCrop->execute(['crop_id' => (int) $cropId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
