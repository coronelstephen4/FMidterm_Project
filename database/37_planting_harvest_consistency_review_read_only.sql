-- Review-only data checks; this file contains SELECT statements only.

-- a. Finds stored expected dates that do not match the selected variety's growing period.
SELECT p.planting_id, p.planting_date, p.expected_harvest_date,
       cv.variety_name, cv.growing_period_days,
       (p.planting_date + cv.growing_period_days)::date AS calculated_expected_date
FROM public.planting p
JOIN public.crop_variety cv ON cv.variety_id = p.variety_id
WHERE p.expected_harvest_date <> (p.planting_date + cv.growing_period_days)::date
ORDER BY p.planting_id;

-- b. Informational data-quality hints only: status is selected manually and is not
-- changed automatically by harvest records. These status/harvest combinations are not errors.
SELECT p.planting_id, ps.status_name,
       CASE WHEN ps.status_name = 'Growing' THEN 'Growing with harvest'
            ELSE 'Harvested with no harvest' END AS issue
FROM public.planting p
JOIN public.planting_statuses ps ON ps.status_id = p.status_id
WHERE (ps.status_name = 'Growing' AND EXISTS (
           SELECT 1 FROM public.harvest h WHERE h.planting_id = p.planting_id
       ))
   OR (ps.status_name = 'Harvested' AND NOT EXISTS (
           SELECT 1 FROM public.harvest h WHERE h.planting_id = p.planting_id
       ))
ORDER BY p.planting_id;

-- c. Finds harvests earlier than the variety's normal growing period, with farm and variety labels.
SELECT h.harvest_id, p.planting_id, h.harvest_date,
       p.planting_date, cv.growing_period_days,
       (p.planting_date + cv.growing_period_days)::date AS usual_harvest_date,
       f.farm_name, c.crop_name, cv.variety_name
FROM public.harvest h
JOIN public.planting p ON p.planting_id = h.planting_id
JOIN public.farm f ON f.farm_id = p.farm_id
JOIN public.crop_variety cv ON cv.variety_id = p.variety_id
JOIN public.crop c ON c.crop_id = cv.crop_id
WHERE h.harvest_date < (p.planting_date + cv.growing_period_days)::date
ORDER BY h.harvest_id;

-- d. Finds only the requested grade/remark combinations for manual review.
SELECT h.harvest_id, q.quality_grade_name, r.remark_description,
       h.harvest_date, h.quantity_kg
FROM public.harvest h
JOIN public.harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
JOIN public.harvest_remarks r ON r.remark_id = h.remark_id
WHERE (q.quality_grade_name = 'Grade A'
       AND r.remark_description IN ('Poor Quality', 'Damaged Harvest'))
   OR (q.quality_grade_name = 'Grade C'
       AND r.remark_description = 'Good Harvest')
ORDER BY h.harvest_id;

-- e. Finds plantings whose area is larger than the area recorded for their farm.
SELECT p.planting_id, f.farm_id, f.farm_name,
       p.area_planted, f.area_hectares
FROM public.planting p
JOIN public.farm f ON f.farm_id = p.farm_id
WHERE p.area_planted > f.area_hectares
ORDER BY p.planting_id;
