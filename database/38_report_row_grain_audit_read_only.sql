-- Read-only report row-grain audit for the six HarvestHub reports.
-- Run each SELECT in Supabase SQL Editor. No database changes are made.
-- Repeated names or equal numeric IDs from different tables do not by themselves
-- indicate duplicate records; use each row's primary key and its parent IDs.

-- 1. Farmers and their farms: one row per farm.
SELECT fm.farmer_id, f.farm_id, fm.first_name, fm.last_name, fm.contact_number,
       fm.address, f.farm_name, f.location, f.area_hectares,
       st.soil_type_name, it.irrigation_type_name
FROM farmer fm
INNER JOIN farm f ON f.farmer_id = fm.farmer_id
INNER JOIN soil_types st ON st.soil_type_id = f.soil_type_id
INNER JOIN irrigation_types it ON it.irrigation_type_id = f.irrigation_type_id
ORDER BY fm.farmer_id ASC, f.farm_id ASC;

-- 2. Crops planted per farm: one row per planting.
SELECT p.planting_id, f.farm_id, fm.farmer_id, cv.variety_id, c.crop_id,
       f.farm_name, fm.first_name, fm.last_name, c.crop_name, cv.variety_name,
       p.planting_date, p.area_planted, p.expected_harvest_date
FROM planting p
INNER JOIN farm f ON f.farm_id = p.farm_id
INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
INNER JOIN crop_variety cv ON cv.variety_id = p.variety_id
INNER JOIN crop c ON c.crop_id = cv.crop_id
ORDER BY f.farm_id ASC, p.planting_date DESC, p.planting_id ASC;

-- 3a. Plantings by status summary: one row per status lookup.
SELECT ps.status_id, ps.status_name, COUNT(p.planting_id) AS planting_count
FROM planting_statuses ps
LEFT JOIN planting p ON p.status_id = ps.status_id
GROUP BY ps.status_id, ps.status_name
ORDER BY ps.status_id;

-- 3b. Plantings by status details: one row per planting.
SELECT p.planting_id, p.farm_id, f.farmer_id, p.variety_id, c.crop_id,
       p.status_id, p.planting_date, p.area_planted, p.expected_harvest_date,
       ps.status_name, f.farm_name, fm.first_name, fm.last_name,
       c.crop_name, cv.variety_name
FROM planting p
INNER JOIN planting_statuses ps ON ps.status_id = p.status_id
INNER JOIN farm f ON f.farm_id = p.farm_id
INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
INNER JOIN crop_variety cv ON cv.variety_id = p.variety_id
INNER JOIN crop c ON c.crop_id = cv.crop_id
ORDER BY p.planting_date DESC, p.planting_id DESC;

-- 4. Harvest records: one row per harvest.
SELECT h.harvest_id, h.planting_id, p.farm_id, f.farmer_id,
       p.variety_id, c.crop_id, h.quality_grade_id, h.remark_id,
       h.harvest_date, h.quantity_kg, h.selling_price_per_kg,
       fm.first_name, fm.last_name, f.farm_name, c.crop_name,
       cv.variety_name, q.quality_grade_name, r.remark_description
FROM harvest h
INNER JOIN planting p ON p.planting_id = h.planting_id
INNER JOIN farm f ON f.farm_id = p.farm_id
INNER JOIN farmer fm ON fm.farmer_id = f.farmer_id
INNER JOIN crop_variety cv ON cv.variety_id = p.variety_id
INNER JOIN crop c ON c.crop_id = cv.crop_id
INNER JOIN harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
INNER JOIN harvest_remarks r ON r.remark_id = h.remark_id
ORDER BY h.harvest_date DESC, h.harvest_id DESC;

-- 5a. Total harvested quantity: one overall summary row.
SELECT COUNT(*) AS harvest_count,
       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg
FROM harvest h;

-- 5b. Total harvested quantity by quality: one row per quality grade.
SELECT q.quality_grade_id, q.quality_grade_name,
       COUNT(h.harvest_id) AS harvest_count,
       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg
FROM harvest_quality_grades q
LEFT JOIN harvest h ON h.quality_grade_id = q.quality_grade_id
GROUP BY q.quality_grade_id, q.quality_grade_name
ORDER BY q.quality_grade_id;

-- 6a. Estimated harvest value: one overall summary row.
SELECT COUNT(*) AS harvest_count,
       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg,
       COALESCE(SUM(h.quantity_kg * h.selling_price_per_kg), 0) AS estimated_value
FROM harvest h;

-- 6b. Estimated harvest value by crop and variety: one row per variety.
SELECT c.crop_id, cv.variety_id, c.crop_name, cv.variety_name,
       COUNT(h.harvest_id) AS harvest_count,
       COALESCE(SUM(h.quantity_kg), 0) AS total_quantity_kg,
       COALESCE(SUM(h.quantity_kg * h.selling_price_per_kg), 0) AS estimated_value
FROM planting p
INNER JOIN crop_variety cv ON cv.variety_id = p.variety_id
INNER JOIN crop c ON c.crop_id = cv.crop_id
INNER JOIN harvest h ON h.planting_id = p.planting_id
GROUP BY c.crop_id, c.crop_name, cv.variety_id, cv.variety_name
ORDER BY estimated_value DESC, c.crop_name, cv.variety_name;
