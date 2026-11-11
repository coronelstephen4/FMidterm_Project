-- DELETES placeholder farmer records and their dependent farm, planting,
-- and harvest rows. Review the matching conditions before running in Supabase.
-- Crop and lookup records are preserved.

BEGIN;

CREATE TEMP TABLE placeholder_farmers ON COMMIT DROP AS
SELECT farmer_id
FROM public.farmer
WHERE LOWER(BTRIM(first_name)) IN ('farm -14', 'farm-14', 'ais')
   OR LOWER(BTRIM(address)) LIKE 'farm-liloan%';

DELETE FROM public.harvest AS harvest
USING public.planting AS planting, public.farm AS farm
WHERE harvest.planting_id = planting.planting_id
  AND planting.farm_id = farm.farm_id
  AND farm.farmer_id IN (SELECT farmer_id FROM placeholder_farmers);

DELETE FROM public.planting AS planting
USING public.farm AS farm
WHERE planting.farm_id = farm.farm_id
  AND farm.farmer_id IN (SELECT farmer_id FROM placeholder_farmers);

DELETE FROM public.farm AS farm
WHERE farm.farmer_id IN (SELECT farmer_id FROM placeholder_farmers);

DELETE FROM public.farmer AS farmer
WHERE farmer.farmer_id IN (SELECT farmer_id FROM placeholder_farmers);

COMMIT;

SELECT COUNT(*) AS remaining_placeholder_farmers
FROM public.farmer
WHERE LOWER(BTRIM(first_name)) IN ('farm -14', 'farm-14', 'ais')
   OR LOWER(BTRIM(address)) LIKE 'farm-liloan%';
