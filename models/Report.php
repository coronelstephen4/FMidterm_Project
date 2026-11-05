<?php
class Report {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function countFarmersAndFarms(string $search, string $address): int {
        $sql = "SELECT COUNT(*)
                FROM farmer fm
                LEFT JOIN farm f ON f.farmer_id = fm.farmer_id
                WHERE (:has_address = FALSE OR fm.address ILIKE :address_filter)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', fm.first_name, fm.last_name, fm.contact_number,
                       fm.address, f.farm_name, f.location) ILIKE :search_filter)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':has_address', $address !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':address_filter', '%' . $address . '%', PDO::PARAM_STR);
        $stmt->bindValue(':has_search', $search !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':search_filter', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getFarmersAndFarms(
        string $search,
        string $address,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $columns = [
            'farmer_id' => 'fm.farmer_id',
            'farmer_name' => 'fm.last_name',
            'address' => 'fm.address',
            'farm_name' => 'f.farm_name',
            'location' => 'f.location',
            'area_hectares' => 'f.area_hectares'
        ];
        $orderBy = $columns[$sort] ?? $columns['farmer_id'];
        $orderDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT fm.farmer_id, fm.first_name, fm.last_name, fm.contact_number,
                       fm.address, fm.date_registered, f.farm_id, f.farm_name, f.location,
                       f.area_hectares, st.soil_type_name, it.irrigation_type_name
                FROM farmer fm
                LEFT JOIN farm f ON f.farmer_id = fm.farmer_id
                LEFT JOIN soil_types st ON st.soil_type_id = f.soil_type_id
                LEFT JOIN irrigation_types it ON it.irrigation_type_id = f.irrigation_type_id
                WHERE (:has_address = FALSE OR fm.address ILIKE :address_filter)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', fm.first_name, fm.last_name, fm.contact_number,
                       fm.address, f.farm_name, f.location) ILIKE :search_filter)
                ORDER BY {$orderBy} {$orderDirection} NULLS LAST, fm.farmer_id ASC, f.farm_id ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':has_address', $address !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':address_filter', '%' . $address . '%', PDO::PARAM_STR);
        $stmt->bindValue(':has_search', $search !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':search_filter', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getFarmOptions(): array {
        $stmt = $this->pdo->query('SELECT farm_id, farm_name FROM farm ORDER BY farm_name, farm_id');
        return $stmt->fetchAll();
    }

    public function countCropsPlantedPerFarm(int $farmId, string $dateFrom, string $dateTo, string $search): int {
        $sql = "SELECT COUNT(*)
                FROM planting p
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                WHERE (:farm_id = 0 OR f.farm_id = :selected_farm_id)
                  AND (:has_date_from = FALSE OR p.planting_date >= :date_from)
                  AND (:has_date_to = FALSE OR p.planting_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', f.farm_name, f.location, fm.first_name,
                       fm.last_name, c.crop_name, c.variety) ILIKE :search_filter)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindCropReportFilters($stmt, $farmId, $dateFrom, $dateTo, $search);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getCropsPlantedPerFarm(
        int $farmId,
        string $dateFrom,
        string $dateTo,
        string $search,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $columns = [
            'farm_name' => 'f.farm_name',
            'farmer_name' => 'fm.last_name',
            'crop_name' => 'c.crop_name',
            'variety' => 'c.variety',
            'planting_date' => 'p.planting_date',
            'area_planted' => 'p.area_planted',
            'status' => 'ps.status_name'
        ];
        $orderBy = $columns[$sort] ?? $columns['farm_name'];
        $orderDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT f.farm_id, f.farm_name, f.location, fm.first_name || ' ' || fm.last_name AS farmer_name,
                       c.crop_name, c.variety, p.planting_date, p.area_planted, p.expected_harvest_date,
                       ps.status_name
                FROM planting p
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                LEFT JOIN planting_statuses ps ON ps.status_id = p.status_id
                WHERE (:farm_id = 0 OR f.farm_id = :selected_farm_id)
                  AND (:has_date_from = FALSE OR p.planting_date >= :date_from)
                  AND (:has_date_to = FALSE OR p.planting_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', f.farm_name, f.location, fm.first_name,
                       fm.last_name, c.crop_name, c.variety) ILIKE :search_filter)
                ORDER BY {$orderBy} {$orderDirection} NULLS LAST, f.farm_id ASC, p.planting_date DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $this->bindCropReportFilters($stmt, $farmId, $dateFrom, $dateTo, $search);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function bindCropReportFilters(PDOStatement $stmt, int $farmId, string $dateFrom, string $dateTo, string $search): void {
        $stmt->bindValue(':farm_id', $farmId, PDO::PARAM_INT);
        $stmt->bindValue(':selected_farm_id', $farmId, PDO::PARAM_INT);
        $stmt->bindValue(':has_date_from', $dateFrom !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':date_from', $dateFrom === '' ? null : $dateFrom, $dateFrom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':has_date_to', $dateTo !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':date_to', $dateTo === '' ? null : $dateTo, $dateTo === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':has_search', $search !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':search_filter', '%' . $search . '%', PDO::PARAM_STR);
    }

    public function getPlantingStatusOptions(): array {
        $stmt = $this->pdo->query('SELECT status_id, status_name FROM planting_statuses ORDER BY status_id');
        return $stmt->fetchAll();
    }

    public function getPlantingStatusSummary(string $dateFrom, string $dateTo): array {
        $sql = "SELECT ps.status_name, COUNT(p.planting_id) AS planting_count
                FROM planting_statuses ps
                LEFT JOIN planting p ON p.status_id = ps.status_id
                  AND (:has_date_from = FALSE OR p.planting_date >= :date_from)
                  AND (:has_date_to = FALSE OR p.planting_date <= :date_to)
                GROUP BY ps.status_id, ps.status_name
                ORDER BY ps.status_id";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countPlantingsByStatus(int $statusId, string $dateFrom, string $dateTo, string $search): int {
        $sql = "SELECT COUNT(*)
                FROM planting p
                INNER JOIN planting_statuses ps ON ps.status_id = p.status_id
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                WHERE (:has_status = FALSE OR ps.status_id = :selected_status_id)
                  AND (:has_date_from = FALSE OR p.planting_date >= :date_from)
                  AND (:has_date_to = FALSE OR p.planting_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', f.farm_name, fm.first_name,
                       fm.last_name, c.crop_name, c.variety, ps.status_name) ILIKE :search_filter)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingsByStatusFilters($stmt, $statusId, $dateFrom, $dateTo, $search);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getPlantingsByStatus(
        int $statusId,
        string $dateFrom,
        string $dateTo,
        string $search,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $columns = [
            'planting_date' => 'p.planting_date', 'status' => 'ps.status_name',
            'farm_name' => 'f.farm_name', 'farmer_name' => 'fm.last_name',
            'crop_name' => 'c.crop_name', 'area_planted' => 'p.area_planted'
        ];
        $orderBy = $columns[$sort] ?? $columns['planting_date'];
        $orderDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT p.planting_id, p.planting_date, p.area_planted, p.expected_harvest_date,
                       ps.status_name, f.farm_name, fm.first_name || ' ' || fm.last_name AS farmer_name,
                       c.crop_name, c.variety
                FROM planting p
                INNER JOIN planting_statuses ps ON ps.status_id = p.status_id
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                WHERE (:has_status = FALSE OR ps.status_id = :selected_status_id)
                  AND (:has_date_from = FALSE OR p.planting_date >= :date_from)
                  AND (:has_date_to = FALSE OR p.planting_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', f.farm_name, fm.first_name,
                       fm.last_name, c.crop_name, c.variety, ps.status_name) ILIKE :search_filter)
                ORDER BY {$orderBy} {$orderDirection} NULLS LAST, p.planting_id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingsByStatusFilters($stmt, $statusId, $dateFrom, $dateTo, $search);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function bindPlantingDates(PDOStatement $stmt, string $dateFrom, string $dateTo): void {
        $stmt->bindValue(':has_date_from', $dateFrom !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':date_from', $dateFrom === '' ? null : $dateFrom, $dateFrom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':has_date_to', $dateTo !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':date_to', $dateTo === '' ? null : $dateTo, $dateTo === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    private function bindPlantingsByStatusFilters(PDOStatement $stmt, int $statusId, string $dateFrom, string $dateTo, string $search): void {
        $stmt->bindValue(':has_status', $statusId > 0, PDO::PARAM_BOOL);
        $stmt->bindValue(':selected_status_id', $statusId, PDO::PARAM_INT);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->bindValue(':has_search', $search !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':search_filter', '%' . $search . '%', PDO::PARAM_STR);
    }

    public function getHarvestQualityOptions(): array {
        $stmt = $this->pdo->query('SELECT quality_grade_id, quality_grade_name FROM harvest_quality_grades ORDER BY quality_grade_id');
        return $stmt->fetchAll();
    }

    public function countHarvestRecords(int $qualityGradeId, string $dateFrom, string $dateTo, string $search): int {
        $sql = "SELECT COUNT(*)
                FROM harvest h
                INNER JOIN planting p ON p.planting_id = h.planting_id
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                INNER JOIN harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
                INNER JOIN harvest_remarks r ON r.remark_id = h.remark_id
                WHERE (:has_quality = FALSE OR q.quality_grade_id = :selected_quality_id)
                  AND (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', fm.first_name, fm.last_name,
                       f.farm_name, c.crop_name, c.variety, q.quality_grade_name,
                       r.remark_description) ILIKE :search_filter)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindHarvestReportFilters($stmt, $qualityGradeId, $dateFrom, $dateTo, $search);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getHarvestRecords(
        int $qualityGradeId,
        string $dateFrom,
        string $dateTo,
        string $search,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $columns = [
            'harvest_date' => 'h.harvest_date', 'farmer_name' => 'fm.last_name',
            'farm_name' => 'f.farm_name', 'crop_name' => 'c.crop_name',
            'quantity_kg' => 'h.quantity_kg', 'quality_grade' => 'q.quality_grade_name'
        ];
        $orderBy = $columns[$sort] ?? $columns['harvest_date'];
        $orderDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT h.harvest_id, h.harvest_date, h.quantity_kg, h.selling_price_per_kg,
                       fm.first_name || ' ' || fm.last_name AS farmer_name, f.farm_name,
                       c.crop_name, c.variety, q.quality_grade_name, r.remark_description
                FROM harvest h
                INNER JOIN planting p ON p.planting_id = h.planting_id
                INNER JOIN farm f ON f.farm_id = p.farm_id
                INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
                INNER JOIN crop c ON c.crop_id = p.crop_id
                INNER JOIN harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
                INNER JOIN harvest_remarks r ON r.remark_id = h.remark_id
                WHERE (:has_quality = FALSE OR q.quality_grade_id = :selected_quality_id)
                  AND (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)
                  AND (:has_search = FALSE OR CONCAT_WS(' ', fm.first_name, fm.last_name,
                       f.farm_name, c.crop_name, c.variety, q.quality_grade_name,
                       r.remark_description) ILIKE :search_filter)
                ORDER BY {$orderBy} {$orderDirection} NULLS LAST, h.harvest_id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $this->bindHarvestReportFilters($stmt, $qualityGradeId, $dateFrom, $dateTo, $search);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function bindHarvestReportFilters(PDOStatement $stmt, int $qualityGradeId, string $dateFrom, string $dateTo, string $search): void {
        $stmt->bindValue(':has_quality', $qualityGradeId > 0, PDO::PARAM_BOOL);
        $stmt->bindValue(':selected_quality_id', $qualityGradeId, PDO::PARAM_INT);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->bindValue(':has_search', $search !== '', PDO::PARAM_BOOL);
        $stmt->bindValue(':search_filter', '%' . $search . '%', PDO::PARAM_STR);
    }

    public function getTotalHarvestQuantity(string $dateFrom, string $dateTo): array {
        $sql = "SELECT COUNT(*) AS harvest_count, COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg
                FROM harvest h
                WHERE (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getHarvestQuantityByQuality(string $dateFrom, string $dateTo): array {
        $sql = "SELECT q.quality_grade_name, COUNT(h.harvest_id) AS harvest_count,
                       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg
                FROM harvest_quality_grades q
                LEFT JOIN harvest h ON h.quality_grade_id = q.quality_grade_id
                  AND (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)
                GROUP BY q.quality_grade_id, q.quality_grade_name
                ORDER BY q.quality_grade_id";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getEstimatedHarvestValue(string $dateFrom, string $dateTo): array {
        $sql = "SELECT COUNT(*) AS harvest_count,
                       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg,
                       COALESCE(SUM(h.quantity_kg * h.selling_price_per_kg), 0) AS estimated_value
                FROM harvest h
                WHERE (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getEstimatedHarvestValueByCrop(string $dateFrom, string $dateTo): array {
        $sql = "SELECT c.crop_name, c.variety, COUNT(h.harvest_id) AS harvest_count,
                       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg,
                       COALESCE(SUM(h.quantity_kg * h.selling_price_per_kg), 0) AS estimated_value
                FROM crop c
                INNER JOIN planting p ON p.crop_id = c.crop_id
                INNER JOIN harvest h ON h.planting_id = p.planting_id
                WHERE (:has_date_from = FALSE OR h.harvest_date >= :date_from)
                  AND (:has_date_to = FALSE OR h.harvest_date <= :date_to)
                GROUP BY c.crop_id, c.crop_name, c.variety
                ORDER BY estimated_value DESC, c.crop_name, c.variety";
        $stmt = $this->pdo->prepare($sql);
        $this->bindPlantingDates($stmt, $dateFrom, $dateTo);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
