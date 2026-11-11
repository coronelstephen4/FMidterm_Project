-- Read-only inventory for reviewing current data before replacing or removing
-- any records. This lists records and relationships; it does not identify a
-- row as synthetic or change the database.
-- Run each SELECT separately in Supabase SQL Editor and compare with verified
-- source records before preparing any data migration.

-- Farmers
SELECT farmer_id, first_name, last_name, contact_number, address, date_registered
FROM public.farmer
ORDER BY farmer_id;

-- Farms with their farmer and lookup labels
SELECT f.farm_id, f.farm_name, f.location, f.area_hectares,
       f.farmer_id, fm.first_name, fm.last_name,
       st.soil_type_name, it.irrigation_type_name
FROM public.farm AS f
JOIN public.farmer AS fm ON fm.farmer_id = f.farmer_id
JOIN public.soil_types AS st ON st.soil_type_id = f.soil_type_id
JOIN public.irrigation_types AS it ON it.irrigation_type_id = f.irrigation_type_id
ORDER BY f.farm_id;

-- Crops with their lookup labels
SELECT c.crop_id, c.crop_name, c.variety, c.growing_period_days,
       cc.crop_category_name, rs.recommended_season_name
FROM public.crop AS c
JOIN public.crop_categories AS cc ON cc.crop_category_id = c.crop_category_id
JOIN public.recommended_seasons AS rs ON rs.recommended_season_id = c.recommended_season_id
ORDER BY c.crop_id;

-- Plantings with their farmer, farm, crop, and status
SELECT p.planting_id, fm.farmer_id, fm.first_name, fm.last_name,
       f.farm_id, f.farm_name, c.crop_id, c.crop_name, c.variety,
       p.planting_date, p.area_planted, p.seed_quantity,
       p.expected_harvest_date, ps.status_name
FROM public.planting AS p
JOIN public.farm AS f ON f.farm_id = p.farm_id
JOIN public.farmer AS fm ON fm.farmer_id = f.farmer_id
JOIN public.crop AS c ON c.crop_id = p.crop_id
JOIN public.planting_statuses AS ps ON ps.status_id = p.status_id
ORDER BY p.planting_id;

-- Harvests with their related planting, farmer, farm, crop, grade, and remark
SELECT h.harvest_id, fm.farmer_id, fm.first_name, fm.last_name,
       f.farm_id, f.farm_name, c.crop_id, c.crop_name, c.variety,
       p.planting_id, p.planting_date, h.harvest_date, h.quantity_kg,
       h.selling_price_per_kg, q.quality_grade_name, r.remark_description
FROM public.harvest AS h
JOIN public.planting AS p ON p.planting_id = h.planting_id
JOIN public.farm AS f ON f.farm_id = p.farm_id
JOIN public.farmer AS fm ON fm.farmer_id = f.farmer_id
JOIN public.crop AS c ON c.crop_id = p.crop_id
JOIN public.harvest_quality_grades AS q ON q.quality_grade_id = h.quality_grade_id
JOIN public.harvest_remarks AS r ON r.remark_id = h.remark_id
ORDER BY h.harvest_id;
