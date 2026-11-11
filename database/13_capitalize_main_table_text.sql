-- Capitalize the first character of text values used by the five main modules.
-- The remaining characters are preserved so acronyms and fixed labels stay intact.
BEGIN;

UPDATE public.farmer
SET first_name = UPPER(LEFT(first_name, 1)) || SUBSTRING(first_name FROM 2),
    last_name = UPPER(LEFT(last_name, 1)) || SUBSTRING(last_name FROM 2),
    address = UPPER(LEFT(address, 1)) || SUBSTRING(address FROM 2);

UPDATE public.farm
SET farm_name = UPPER(LEFT(farm_name, 1)) || SUBSTRING(farm_name FROM 2),
    location = UPPER(LEFT(location, 1)) || SUBSTRING(location FROM 2);

UPDATE public.crop
SET crop_name = UPPER(LEFT(crop_name, 1)) || SUBSTRING(crop_name FROM 2),
    variety = UPPER(LEFT(variety, 1)) || SUBSTRING(variety FROM 2);

-- Planting and harvest display labels are stored in normalized lookup tables.
UPDATE public.planting_statuses
SET status_name = UPPER(LEFT(status_name, 1)) || SUBSTRING(status_name FROM 2);

UPDATE public.harvest_quality_grades
SET quality_grade_name = UPPER(LEFT(quality_grade_name, 1)) || SUBSTRING(quality_grade_name FROM 2);

UPDATE public.harvest_remarks
SET remark_description = UPPER(LEFT(remark_description, 1)) || SUBSTRING(remark_description FROM 2);

UPDATE public.soil_types
SET soil_type_name = UPPER(LEFT(soil_type_name, 1)) || SUBSTRING(soil_type_name FROM 2);

UPDATE public.irrigation_types
SET irrigation_type_name = UPPER(LEFT(irrigation_type_name, 1)) || SUBSTRING(irrigation_type_name FROM 2);

UPDATE public.crop_categories
SET crop_category_name = UPPER(LEFT(crop_category_name, 1)) || SUBSTRING(crop_category_name FROM 2);

UPDATE public.recommended_seasons
SET recommended_season_name = UPPER(LEFT(recommended_season_name, 1)) || SUBSTRING(recommended_season_name FROM 2);

COMMIT;

-- Verify that every farmer, farm, and crop text value starts with a capital letter.
SELECT 'farmer' AS table_name, farmer_id AS record_id, first_name AS value
FROM public.farmer
WHERE LEFT(first_name, 1) <> UPPER(LEFT(first_name, 1))
   OR LEFT(last_name, 1) <> UPPER(LEFT(last_name, 1))
   OR LEFT(address, 1) <> UPPER(LEFT(address, 1))
UNION ALL
SELECT 'farm', farm_id, farm_name
FROM public.farm
WHERE LEFT(farm_name, 1) <> UPPER(LEFT(farm_name, 1))
   OR LEFT(location, 1) <> UPPER(LEFT(location, 1))
UNION ALL
SELECT 'crop', crop_id, crop_name
FROM public.crop
WHERE LEFT(crop_name, 1) <> UPPER(LEFT(crop_name, 1))
   OR LEFT(variety, 1) <> UPPER(LEFT(variety, 1))
UNION ALL
SELECT 'planting_statuses', status_id, status_name
FROM public.planting_statuses
WHERE LEFT(status_name, 1) <> UPPER(LEFT(status_name, 1))
UNION ALL
SELECT 'harvest_quality_grades', quality_grade_id, quality_grade_name
FROM public.harvest_quality_grades
WHERE LEFT(quality_grade_name, 1) <> UPPER(LEFT(quality_grade_name, 1))
UNION ALL
SELECT 'harvest_remarks', remark_id, remark_description
FROM public.harvest_remarks
WHERE LEFT(remark_description, 1) <> UPPER(LEFT(remark_description, 1))
UNION ALL
SELECT 'soil_types', soil_type_id, soil_type_name
FROM public.soil_types
WHERE LEFT(soil_type_name, 1) <> UPPER(LEFT(soil_type_name, 1))
UNION ALL
SELECT 'irrigation_types', irrigation_type_id, irrigation_type_name
FROM public.irrigation_types
WHERE LEFT(irrigation_type_name, 1) <> UPPER(LEFT(irrigation_type_name, 1))
UNION ALL
SELECT 'crop_categories', crop_category_id, crop_category_name
FROM public.crop_categories
WHERE LEFT(crop_category_name, 1) <> UPPER(LEFT(crop_category_name, 1))
UNION ALL
SELECT 'recommended_seasons', recommended_season_id, recommended_season_name
FROM public.recommended_seasons
WHERE LEFT(recommended_season_name, 1) <> UPPER(LEFT(recommended_season_name, 1));
