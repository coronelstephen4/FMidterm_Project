-- Read-only check: each row reports non-null text values and those without an FK mapping.
-- An unmapped count of zero means all populated source values have a lookup ID.

SELECT 'planting.status' AS field,
       COUNT(p.status) AS populated_rows,
       COUNT(p.status) FILTER (WHERE p.status_id IS NULL) AS unmapped_rows
FROM planting AS p

UNION ALL

SELECT 'harvest.quality_grade',
       COUNT(h.quality_grade),
       COUNT(h.quality_grade) FILTER (WHERE h.quality_grade_id IS NULL)
FROM harvest AS h

UNION ALL

SELECT 'farm.irrigation_type',
       COUNT(f.irrigation_type),
       COUNT(f.irrigation_type) FILTER (WHERE f.irrigation_type_id IS NULL)
FROM farm AS f

UNION ALL

SELECT 'farm.soil_type',
       COUNT(f.soil_type),
       COUNT(f.soil_type) FILTER (WHERE f.soil_type_id IS NULL)
FROM farm AS f

UNION ALL

SELECT 'crop.crop_category',
       COUNT(c.crop_category),
       COUNT(c.crop_category) FILTER (WHERE c.crop_category_id IS NULL)
FROM crop AS c

UNION ALL

SELECT 'crop.recommended_season',
       COUNT(c.recommended_season),
       COUNT(c.recommended_season) FILTER (WHERE c.recommended_season_id IS NULL)
FROM crop AS c

ORDER BY field;
