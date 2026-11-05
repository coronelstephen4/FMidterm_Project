<?php
class Lookup {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getHarvestRemarks(): array {
        $sql = "SELECT remark_id, remark_description
                FROM harvest_remarks
                ORDER BY remark_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getHarvestQualityGrades(): array {
        $sql = "SELECT quality_grade_id, quality_grade_name
                FROM harvest_quality_grades
                ORDER BY quality_grade_id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getPlantingStatuses(): array {
        $sql = "SELECT status_id, status_name
                FROM planting_statuses
                ORDER BY status_id";
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

    public function getCropCategories(): array {
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
}
