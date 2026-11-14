-- SUPERSEDED by database/36_farmer_farm_duplicate_indexes.sql. Do not run this file.
-- It contains the old four-table policy and is retained only as history.
-- Review-only composite duplicate rules.
-- Run the read-only pre-checks first and review any returned IDs.
-- Do not apply this file until existing duplicate rows have been resolved.

-- Section 1: read-only duplicate pre-checks.

SELECT
    lower(btrim(first_name)) AS normalized_first_name,
    lower(btrim(last_name)) AS normalized_last_name,
    contact_number,
    lower(regexp_replace(btrim(address), '\s+', ' ', 'g')) AS normalized_address,
    array_agg(farmer_id ORDER BY farmer_id) AS farmer_ids,
    count(*) AS duplicate_count
FROM public.farmer
GROUP BY lower(btrim(first_name)),
         lower(btrim(last_name)),
         contact_number,
         lower(regexp_replace(btrim(address), '\s+', ' ', 'g'))
HAVING count(*) > 1
ORDER BY farmer_ids;

SELECT
    farmer_id,
    lower(btrim(farm_name)) AS normalized_farm_name,
    lower(regexp_replace(btrim(location), '\s+', ' ', 'g')) AS normalized_location,
    area_hectares,
    irrigation_type_id,
    soil_type_id,
    array_agg(farm_id ORDER BY farm_id) AS farm_ids,
    count(*) AS duplicate_count
FROM public.farm
GROUP BY farmer_id,
         lower(btrim(farm_name)),
         lower(regexp_replace(btrim(location), '\s+', ' ', 'g')),
         area_hectares,
         irrigation_type_id,
         soil_type_id
HAVING count(*) > 1
ORDER BY farm_ids;

SELECT
    farm_id,
    variety_id,
    planting_date,
    area_planted,
    seed_quantity,
    expected_harvest_date,
    status_id,
    array_agg(planting_id ORDER BY planting_id) AS planting_ids,
    count(*) AS duplicate_count
FROM public.planting
GROUP BY farm_id,
         variety_id,
         planting_date,
         area_planted,
         seed_quantity,
         expected_harvest_date,
         status_id
HAVING count(*) > 1
ORDER BY planting_ids;

SELECT
    planting_id,
    harvest_date,
    quantity_kg,
    selling_price_per_kg,
    remark_id,
    quality_grade_id,
    array_agg(harvest_id ORDER BY harvest_id) AS harvest_ids,
    count(*) AS duplicate_count
FROM public.harvest
GROUP BY planting_id,
         harvest_date,
         quantity_kg,
         selling_price_per_kg,
         remark_id,
         quality_grade_id
HAVING count(*) > 1
ORDER BY harvest_ids;

-- Section 2: reviewed unique indexes. These statements are not run by the app.

BEGIN;

CREATE UNIQUE INDEX IF NOT EXISTS farmer_identity_uq
    ON public.farmer (
        lower(btrim(first_name)),
        lower(btrim(last_name)),
        contact_number,
        lower(regexp_replace(btrim(address), '\s+', ' ', 'g'))
    );

CREATE UNIQUE INDEX IF NOT EXISTS farm_identity_uq
    ON public.farm (
        farmer_id,
        lower(btrim(farm_name)),
        lower(regexp_replace(btrim(location), '\s+', ' ', 'g')),
        area_hectares,
        irrigation_type_id,
        soil_type_id
    );

CREATE UNIQUE INDEX IF NOT EXISTS planting_identity_uq
    ON public.planting (
        farm_id,
        variety_id,
        planting_date,
        area_planted,
        seed_quantity,
        expected_harvest_date,
        status_id
    );

CREATE UNIQUE INDEX IF NOT EXISTS harvest_identity_uq
    ON public.harvest (
        planting_id,
        harvest_date,
        quantity_kg,
        selling_price_per_kg,
        remark_id,
        quality_grade_id
    );

COMMIT;

-- Section 3: remove old data-duplicate guards after reviewing the indexes.
-- The historical Farmer and Farm guards checked only subsets of the new rules.
-- Planting and Harvest guards checked the full matching fields but are redundant
-- once the corresponding unique indexes are installed. Crop rules are untouched.

DROP TRIGGER IF EXISTS farmer_duplicate_guard ON public.farmer;
DROP TRIGGER IF EXISTS farm_duplicate_guard ON public.farm;
DROP TRIGGER IF EXISTS planting_duplicate_guard ON public.planting;
DROP TRIGGER IF EXISTS harvest_duplicate_guard ON public.harvest;
