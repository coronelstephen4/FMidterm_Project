-- Read-only data-quality review for possible repeated records in the five core tables.
-- This script returns candidate rows; it does not update or delete anything.

-- Farmers: same normalized contact plus address, or same full name plus address.
SELECT a.farmer_id AS first_id,
       b.farmer_id AS possible_duplicate_id,
       a.first_name || ' ' || a.last_name AS first_name,
       b.first_name || ' ' || b.last_name AS possible_duplicate_name,
       a.contact_number AS first_contact,
       b.contact_number AS possible_duplicate_contact,
       a.address AS first_address,
       b.address AS possible_duplicate_address,
       CASE
           WHEN regexp_replace(btrim(a.contact_number), '\D', '', 'g') =
                regexp_replace(btrim(b.contact_number), '\D', '', 'g')
            AND lower(regexp_replace(btrim(a.address), '\s+', ' ', 'g')) =
                lower(regexp_replace(btrim(b.address), '\s+', ' ', 'g'))
           THEN 'Same contact and address'
           ELSE 'Same full name and address'
       END AS match_reason
FROM public.farmer a
JOIN public.farmer b ON b.farmer_id > a.farmer_id
WHERE (
    regexp_replace(btrim(a.contact_number), '\D', '', 'g') =
    regexp_replace(btrim(b.contact_number), '\D', '', 'g')
    AND lower(regexp_replace(btrim(a.address), '\s+', ' ', 'g')) =
        lower(regexp_replace(btrim(b.address), '\s+', ' ', 'g'))
) OR (
    lower(btrim(a.first_name)) = lower(btrim(b.first_name))
    AND lower(btrim(a.last_name)) = lower(btrim(b.last_name))
    AND lower(regexp_replace(btrim(a.address), '\s+', ' ', 'g')) =
        lower(regexp_replace(btrim(b.address), '\s+', ' ', 'g'))
)
ORDER BY a.farmer_id, b.farmer_id;

-- Farms: same owner and normalized farm name.
SELECT a.farm_id AS first_id, b.farm_id AS possible_duplicate_id,
       a.farmer_id, a.farm_name
FROM public.farm a
JOIN public.farm b ON b.farm_id > a.farm_id AND b.farmer_id = a.farmer_id
WHERE lower(btrim(a.farm_name)) = lower(btrim(b.farm_name))
ORDER BY a.farm_id, b.farm_id;

-- Crops: same normalized crop name and variety.
SELECT a.crop_id AS first_id, b.crop_id AS possible_duplicate_id,
       a.crop_name, a.variety
FROM public.crop a
JOIN public.crop b ON b.crop_id > a.crop_id
WHERE lower(btrim(a.crop_name)) = lower(btrim(b.crop_name))
  AND lower(btrim(a.variety)) = lower(btrim(b.variety))
ORDER BY a.crop_id, b.crop_id;

-- Plantings: same farm, crop, dates, quantities, and status.
SELECT a.planting_id AS first_id, b.planting_id AS possible_duplicate_id,
       a.farm_id, a.crop_id, a.planting_date, a.area_planted,
       a.seed_quantity, a.expected_harvest_date, a.status_id
FROM public.planting a
JOIN public.planting b ON b.planting_id > a.planting_id
WHERE (a.farm_id, a.crop_id, a.planting_date, a.area_planted,
       a.seed_quantity, a.expected_harvest_date, a.status_id)
   IS NOT DISTINCT FROM
      (b.farm_id, b.crop_id, b.planting_date, b.area_planted,
       b.seed_quantity, b.expected_harvest_date, b.status_id)
ORDER BY a.planting_id, b.planting_id;

-- Harvests: same planting, date, quantity, price, grade, and remark.
SELECT a.harvest_id AS first_id, b.harvest_id AS possible_duplicate_id,
       a.planting_id, a.harvest_date, a.quantity_kg,
       a.selling_price_per_kg, a.quality_grade_id, a.remark_id
FROM public.harvest a
JOIN public.harvest b ON b.harvest_id > a.harvest_id
WHERE (a.planting_id, a.harvest_date, a.quantity_kg,
       a.selling_price_per_kg, a.quality_grade_id, a.remark_id)
   IS NOT DISTINCT FROM
      (b.planting_id, b.harvest_date, b.quantity_kg,
       b.selling_price_per_kg, b.quality_grade_id, b.remark_id)
ORDER BY a.harvest_id, b.harvest_id;
